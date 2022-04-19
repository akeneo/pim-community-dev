<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class InvalidData extends \Exception
{
    public function __construct(private ConstraintViolationListInterface $violations)
    {
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
