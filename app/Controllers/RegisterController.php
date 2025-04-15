<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegister;
use CodeIgniter\Shield\Exceptions\ValidationException;
use CodeIgniter\Validation\Validation;

class RegisterController extends ShieldRegister
{
    /**
     * Attempts to register the user.
     */
    #[\Override]
    public function registerAction(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()
                ->withInput()
                ->with('error', lang('Auth.registerDisabled'));
        }

        $users = $this->getUserProvider();

        // Validate here first, since some things,
        // like the password, can only be validated properly here.
        $rules = $this->getValidationRules();

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules, [], config('Auth')->DBGroup);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $validData = $this->validator->getValidated();

        // Save the user
        $user = $this->getUserEntity();
        $user->fill($validData);

        // Workaround for email only registration/login
        if ($user->username === null) {
            $user->username = null;
        }

        // save the gravatar into public/avatars folder
        $user->avatar_path = save_gravatar($user);

        try {
            $users->save($user);
        } catch (ValidationException) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $users->errors());
        }

        // To get the complete user object with ID, we need to get from the database
        $user = $users->findById($users->getInsertID());

        assert($user instanceof User);

        // Add to default group
        $users->addToDefaultGroup($user);

        Events::trigger('register', $user);

        /** @var Session $authenticator */
        $authenticator = auth('session')
            ->getAuthenticator();

        $authenticator->startLogin($user);

        // If an action has been defined for register, start it up.
        $hasAction = $authenticator->startUpAction('register', $user);
        if ($hasAction) {
            return redirect()->route('auth-action-show');
        }

        // Set the user active
        $user->activate();

        $authenticator->completeLogin($user);

        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', lang('Auth.registerSuccess'));
    }
}
