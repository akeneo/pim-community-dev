<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Symfony\Component\Validator\Constraints\NotBlank;
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

    /** @var ExtrapolatedAttributeValidator */
    private $extrapolatedAttributeValidator;

    public function __construct(RuleEngineValidatorACLInterface $ruleEngineValidatorACL, ExtrapolatedAttributeValidator $extrapolatedAttributeValidator)
    {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->extrapolatedAttributeValidator = $extrapolatedAttributeValidator;
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
        $violations = $this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
            $productAssignment['attribute'],
            $assetFamilyIdentifier,
            [TextAttribute::ATTRIBUTE_TYPE]
        );

        if (isset($productAssignment['channel'])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
                $productAssignment['channel'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        if (isset($productAssignment['locale'])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
                $productAssignment['locale'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
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
