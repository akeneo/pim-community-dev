<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake\Anticorruption;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\RuleEngineValidatorACLInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleEngineValidatorACLStub implements RuleEngineValidatorACLInterface
{
    protected ConstraintViolationListInterface $violations;

    public function __construct()
    {
        $this->violations = new ConstraintViolationList();
    }

    public function validateProductSelection(array $normalizedProductSelection): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function validateProductAssignment(array $normalizedProductAssignment): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function stubWithViolationMessage(string $message): void
    {
        $this->violations->add(
            new ConstraintViolation(
                $message,
                '',
                [],
                '',
                '',
                ''
            )
        );
    }
}
