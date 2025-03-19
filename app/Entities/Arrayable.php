<?php

declare(strict_types=1);

namespace App\Entities;

interface Arrayable
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
