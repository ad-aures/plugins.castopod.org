<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Michalsn\CodeIgniterHtmx\HTTP\IncomingRequest;
use Michalsn\CodeIgniterHtmx\HTTP\Response;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var IncomingRequest
     */
    protected $request;

    /**
     * Instance of the main response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['alerts', 'misc'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    #[\Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * @param array<string,string>|string $message
     */
    protected function alert(
        string $type,
        array|string $message,
        bool $withInput = false,
    ): string | RedirectResponse {
        if (is_string($message)) {
            $message = [
                'error' => $message,
            ];
        }

        if ($this->request->isHtmx()) {
            return htmx_alert($type, $message);
        }

        $response = redirect()
            ->back()
            ->with($type, $message);
        if ($withInput) {
            $response->withInput();
        }

        return $response;
    }
}
