<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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

    public function validate(array $productAssignments, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $violations = $this->checkNotEmpty($productAssignments);
        foreach ($productAssignments as $productAssignment) {
            $violations->addAll($this->validateProductAssignment($productAssignment, $assetFamilyIdentifier));
        }

        return $violations;
    }

    private function validateProductAssignment(array $productAssignment, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        if ($this->hasAnyExtrapolation($productAssignment)) {
            return $this->checkExtrapolatedAttributes($productAssignment, $assetFamilyIdentifier);
        }

        return $this->ruleEngineValidatorACL->validateProductSelection($productAssignment);
    }

    private function hasAnyExtrapolation(array $productAssignment): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productAssignment['attribute']);
        $isLocaleExtrapolated = isset($productAssignment['locale']) ? ReplacePattern::isExtrapolation($productAssignment['locale']) : false;
        $isChannelExtrapolated = isset($productAssignment['channel']) ? ReplacePattern::isExtrapolation($productAssignment['channel']) : false;

        return $isFieldExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(array $productAssignment, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $extrapolatedAttributeCodes = ReplacePattern::detectPatterns($productAssignment['attribute']);
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();
        foreach ($extrapolatedAttributeCodes as $extrapolatedAttributeCode) {
            $isAttributeExisting = $this->attributeExists->withAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AttributeCode::fromString($extrapolatedAttributeCode)
            );
            $violations = $validator->validate(
                $isAttributeExisting,
                new Callback(function ($attributeExists, ExecutionContextInterface $context) use ($extrapolatedAttributeCode) {
                    if (!$attributeExists) {
                        $context
                            ->buildViolation(ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_SHOULD_EXIST, ['%attribute_code%' => $extrapolatedAttributeCode])
                            ->addViolation();
                    }
                })
            );
        }

        return $violations;
    }

    private function checkNotEmpty(array $productAssignments): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productAssignments,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]
        );

        return $ruleEngineViolations;
    }
}
