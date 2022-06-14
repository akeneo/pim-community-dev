<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class InvalidPassword extends \Exception
{
    public function __construct(private ConstraintViolationListInterface $violations)
    {
    }

    public function violations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
