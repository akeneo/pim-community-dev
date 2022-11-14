<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;

final class ContributorEmail
{
    private function __construct(private string $email)
    {
        if (false === \filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The contributor email must be valid.');
        }
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
