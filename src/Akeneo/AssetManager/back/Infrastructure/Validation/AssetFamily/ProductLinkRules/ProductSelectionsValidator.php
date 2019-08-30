<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
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

    /** @var GetAttributeTypeInterface */
    private $getAttributeType;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        AttributeExistsInterface $attributeExists,
        GetAttributeTypeInterface $getAttributeType
    ) {
        $this->attributeExists = $attributeExists;
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->getAttributeType = $getAttributeType;
    }

    public function validate(array $productSelections, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $violationList = $this->checkNotEmpty($productSelections);
        foreach ($productSelections as $productSelection) {
            $violationList->addAll($this->validateProductSelection($productSelection, $assetFamilyIdentifier));
        }

        return $violationList;
    }

    private function checkNotEmpty(array $productSelections): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $ruleEngineViolations = $validator->validate($productSelections,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_SELECTION_CANNOT_BE_EMPTY])]
        );

        return $ruleEngineViolations;
    }

    private function validateProductSelection(
        array $productSelection,
        string $assetFamilyIdentifier
    ): ConstraintViolationListInterface {
        if ($this->hasAnyExtrapolation($productSelection)) {
            return $this->checkExtrapolatedAttributes($productSelection, $assetFamilyIdentifier);
        }

        return $this->ruleEngineValidatorACL->validateProductSelection($productSelection);
    }

    private function hasAnyExtrapolation(array $productSelection): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productSelection['field']);
        $isValueExtrapolated = ReplacePattern::isExtrapolation($productSelection['value']);
        $isLocaleExtrapolated = isset($productSelection['locale'])
            ? ReplacePattern::isExtrapolation($productSelection['locale']) : false;
        $isChannelExtrapolated = isset($productSelection['channel'])
            ? ReplacePattern::isExtrapolation($productSelection['channel']) : false;

        return $isFieldExtrapolated || $isValueExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(
        array $productSelection,
        string $assetFamilyIdentifier
    ): ConstraintViolationListInterface {
        $violations = $this->checkAttributeExistsAndHasASupportedType(
            $productSelection['field'],
            $assetFamilyIdentifier,
            [TextAttribute::ATTRIBUTE_TYPE]
        );
        $violations->addAll($this->checkAttributeExistsAndHasASupportedType(
            $productSelection['value'],
            $assetFamilyIdentifier,
            [
                TextAttribute::ATTRIBUTE_TYPE,
                OptionAttribute::ATTRIBUTE_TYPE,
                OptionCollectionAttribute::ATTRIBUTE_TYPE
            ]
        ));
        if (isset($productSelection['channel'])) {
            $violations->addAll($this->checkAttributeExistsAndHasASupportedType(
                $productSelection['channel'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        if (isset($productSelection['locale'])) {
            $violations->addAll($this->checkAttributeExistsAndHasASupportedType(
                $productSelection['locale'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        return $violations;
    }

    private function checkAttributeExistsAndHasASupportedType($fieldValue, string $assetFamilyIdentifier, array $supportedTypes): ConstraintViolationListInterface
    {
        $allViolations = new ConstraintViolationList();
        $fieldAttributeCodes = ReplacePattern::detectPatterns($fieldValue);
        foreach ($fieldAttributeCodes as $fieldAttributeCode) {
            $violations = $this->checkAttributeExists($assetFamilyIdentifier, $fieldAttributeCode);
            if (0 === $violations->count()) {
                $violations->addAll(
                    $this->checkAttributeTypeIsSupported($assetFamilyIdentifier, $fieldAttributeCode, $supportedTypes)
                );
            }
            $allViolations->addAll($violations);
        }

        return $allViolations;
    }

    private function checkAttributeExists(
        string $assetFamilyIdentifier,
        $extrapolatedAttributeCode
    ): ConstraintViolationListInterface {
        $validator = Validation::createValidator();
        $isAttributeExisting = $this->attributeExists->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($extrapolatedAttributeCode)
        );

        return $validator->validate(
            $isAttributeExisting,
            new Callback(function ($attributeExists, ExecutionContextInterface $context) use (
                $extrapolatedAttributeCode
            ) {
                if (!$attributeExists) {
                    $context
                        ->buildViolation(ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_SHOULD_EXIST,
                            ['%attribute_code%' => $extrapolatedAttributeCode]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }

    private function checkAttributeTypeIsSupported(
        string $assetFamilyIdentifier,
        string $attributeCode,
        array $supportedAttributeTypes
    ): ConstraintViolationListInterface {
        $validator = Validation::createValidator();
        $attributeType = $this->getAttributeType->fetch(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );
        $isAttributeTypeSupported = in_array($attributeType, $supportedAttributeTypes);

        return $validator->validate(
            $isAttributeTypeSupported,
            new Callback(function ($isAttributeTypeSupported, ExecutionContextInterface $context) use ($attributeCode, $attributeType, $supportedAttributeTypes) {
                if (!$isAttributeTypeSupported) {
                    $context
                        ->buildViolation(
                            ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_TYPE_SHOULD_BE_SUPPORTED,
                            [
                                '%attribute_code%' => $attributeCode,
                                '%attribute_type%' => $attributeType,
                                '%supported_attribute_type%' => implode(', ', $supportedAttributeTypes)
                            ]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }
}
