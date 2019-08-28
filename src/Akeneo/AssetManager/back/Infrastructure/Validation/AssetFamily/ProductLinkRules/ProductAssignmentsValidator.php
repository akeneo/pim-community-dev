<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductAssignmentsValidator
{
    /** @var RuleEngineValidatorACLInterface */
    private $ruleEngineValidatorACL;

    /** @var AttributeExistsInterface */
    private $attributeExists;

    public function __construct(RuleEngineValidatorACLInterface $ruleEngineValidatorACL, AttributeExistsInterface $attributeExists)
    {
        $this->attributeExists = $attributeExists;
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
    }

    public function validate(array $productAssignments, string $assetFamilyIdentifier): ConstraintViolationList
    {
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productAssignments, [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]);
        foreach ($productAssignments as $productAssignment) {
            $ruleEngineViolations = $this->ruleEngineValidatorACL->validateProductAssignment($productAssignment);
        }

        return $ruleEngineViolations;
    }
}
