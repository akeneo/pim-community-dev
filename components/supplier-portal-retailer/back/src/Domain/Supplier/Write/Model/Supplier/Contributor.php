<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Contributor\Email;

final class Contributor
{
    private Email $email;

    private function __construct(string $email)
    {
        $this->email = Email::fromString($email);
    }

    public static function fromEmail(string $email): self
    {
        return new self($email);
    }

    public function email(): string
    {
        return (string) $this->email;
    }
}
