<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface RuleEngineValidatorACLInterface
{
    public function validateProductCondition(array $normalizedProductCondition): ConstraintViolationListInterface;

    public function validateProductAction(array $normalizedProductAction): ConstraintViolationListInterface;
}
