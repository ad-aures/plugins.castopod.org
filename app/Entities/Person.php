<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\HTTP\URI;
use JsonSerializable;

class Person implements JsonSerializable
{
    public private(set) string $name;
    public private(set) ?string $email = null;
    public private(set) ?URI $url = null;

    /**
     * @param array{name:string,email?:string,url?:string} $data
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'] ?? null;

        if (array_key_exists('url', $data)) {
            $this->url = new URI($data['url']);
        }
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'name' => $this->name,
        ];

        if ($this->email) {
            $data['email'] = $this->email;
        }

        if ($this->url instanceof \CodeIgniter\HTTP\URI) {
            $data['url'] = (string) $this->url;
        }

        return $data;
    }
}
