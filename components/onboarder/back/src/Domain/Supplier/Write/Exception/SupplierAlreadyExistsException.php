<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Exception;

final class SupplierAlreadyExistsException extends \DomainException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('A supplier with code "%s" already exist', $code));
    }
}
