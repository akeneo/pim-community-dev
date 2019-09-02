<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ExtrapolatedAttributeValidator
{
    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var GetAttributeTypeInterface */
    private $getAttributeType;

    public function __construct(AttributeExistsInterface $attributeExists, GetAttributeTypeInterface $getAttributeType)
    {
        $this->attributeExists = $attributeExists;
        $this->getAttributeType = $getAttributeType;
    }

    public function checkAttributeExistsAndHasASupportedType(
        $fieldValue,
        string $assetFamilyIdentifier,
        array $supportedTypes
    ): ConstraintViolationListInterface {
        $allViolations = new ConstraintViolationList();
        $fieldAttributeCodes = ReplacePattern::detectPatterns($fieldValue);
        foreach ($fieldAttributeCodes as $fieldAttributeCode) {
            $violations = $this->checkAttributeExists($assetFamilyIdentifier, $fieldAttributeCode);
            if (0 === $violations->count()) {
                $allViolations->addAll(
                    $this->checkAttributeTypeIsSupported($assetFamilyIdentifier, $fieldAttributeCode, $supportedTypes)
                );
            }
            $allViolations->addAll($violations);
        }

        return $allViolations;
    }

    public function checkAttributeExists(
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

    public function checkAttributeTypeIsSupported(
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
            new Callback(function ($isAttributeTypeSupported, ExecutionContextInterface $context) use (
                $attributeCode,
                $attributeType,
                $supportedAttributeTypes
            ) {
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
            })
        );
    }
}
