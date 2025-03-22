<?php

namespace App\DTOs;

class HttpStatusResultDTO
{
    public function __construct(
        public readonly int|string $status,
        public readonly string|null $redirectTo,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            redirectTo: $data['redirectTo'],
        );
    }
}
