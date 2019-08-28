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
class ProductSelectionsValidator
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

    public function validate(array $productSelections, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $violationList = $this->checkNotEmpty($productSelections);
        foreach ($productSelections as $productSelection) {
            $violationList->addAll($this->validateProductSelection($productSelection, $assetFamilyIdentifier));
        }

        return $violationList;
    }

    private function validateProductSelection(array $productSelection, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        if ($this->hasAnyExtrapolation($productSelection)) {
            return $this->checkExtrapolatedAttributes($productSelection, $assetFamilyIdentifier);
        }

        return $this->ruleEngineValidatorACL->validateProductSelection($productSelection);
    }

    private function hasAnyExtrapolation(array $productSelection): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productSelection['field']);
        $isValueExtrapolated = ReplacePattern::isExtrapolation($productSelection['value']);
        $isLocaleExtrapolated = isset($productSelection['locale']) ? ReplacePattern::isExtrapolation($productSelection['locale']) : false;
        $isChannelExtrapolated = isset($productSelection['channel']) ? ReplacePattern::isExtrapolation($productSelection['channel']) : false;

        return $isFieldExtrapolated || $isValueExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(array $productSelection, string $assetFamilyIdentifier): ConstraintViolationList
    {
        $extrapolatedAttributeCodes = array_merge(
            ReplacePattern::detectPatterns($productSelection['field']),
            ReplacePattern::detectPatterns($productSelection['value'])
        );
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

    /**
     * @param array $productSelections
     *
     * @return ConstraintViolationListInterface
     *
     */
    private function checkNotEmpty(array $productSelections): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productSelections,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_SELECTION_CANNOT_BE_EMPTY])]
        );

        return $ruleEngineViolations;
}
}
