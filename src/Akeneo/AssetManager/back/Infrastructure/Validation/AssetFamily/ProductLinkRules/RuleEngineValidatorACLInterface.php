<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface RuleEngineValidatorACLInterface
{
    public function validateProductSelection(array $normalizedProductSelection): ConstraintViolationListInterface;

    public function validateProductAssignment(array $normalizedProductAssignment): ConstraintViolationListInterface;
}
