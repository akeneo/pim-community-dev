<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class InvalidSupplierFile extends \Exception
{
    public function __construct(private ConstraintViolationListInterface $violations)
    {
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
