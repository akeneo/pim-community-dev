<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeHasOneValuePerChannelInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeHasOneValuePerLocaleInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Attribute\Code;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
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
    private AttributeExistsInterface $attributeExists;

    private GetAttributeTypeInterface $getAttributeType;

    private AttributeHasOneValuePerChannelInterface $attributeHasOneValuePerChannel;

    private AttributeHasOneValuePerLocaleInterface $attributeHasOneValuePerLocale;

    public function __construct(
        AttributeExistsInterface $attributeExists,
        GetAttributeTypeInterface $getAttributeType,
        AttributeHasOneValuePerChannelInterface $attributeHasOneValuePerChannel,
        AttributeHasOneValuePerLocaleInterface $attributeHasOneValuePerLocale
    ) {
        $this->attributeExists = $attributeExists;
        $this->getAttributeType = $getAttributeType;
        $this->attributeHasOneValuePerChannel = $attributeHasOneValuePerChannel;
        $this->attributeHasOneValuePerLocale = $attributeHasOneValuePerLocale;
    }

    /**
     * Check:
     * - the attribute exists
     * - has one of supported type ($supportedTypes)
     * - has not one value per channel
     * - has not one value per locale
     *
     * @param mixed  $fieldValue
     * @param string $assetFamilyIdentifier
     * @param array  $supportedTypes
     *
     * @return ConstraintViolationListInterface
     */
    public function checkAttribute(
        $fieldValue,
        string $assetFamilyIdentifier,
        array $supportedTypes
    ): ConstraintViolationListInterface {
        $allViolations = new ConstraintViolationList();
        $fieldAttributeCodes = ReplacePattern::detectPatterns($fieldValue);
        foreach ($fieldAttributeCodes as $fieldAttributeCode) {
            $violations = $this->validateAttributeCode($fieldAttributeCode);
            if ($violations->count() === 0) {
                $violations->addAll($this->checkAttributeExists($assetFamilyIdentifier, $fieldAttributeCode));
                if (0 === $violations->count()) {
                    $allViolations->addAll(
                        $this->checkAttributeTypeIsSupported($assetFamilyIdentifier, $fieldAttributeCode, $supportedTypes)
                    );
                    $allViolations->addAll(
                        $this->checkHasNotOneValuePerChannel($assetFamilyIdentifier, $fieldAttributeCode)
                    );
                    $allViolations->addAll(
                        $this->checkHasNotOneValuePerLocale($assetFamilyIdentifier, $fieldAttributeCode)
                    );
                }
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

    private function checkHasNotOneValuePerChannel(
        string $assetFamilyIdentifier,
        string $attributeCode
    ): ConstraintViolationListInterface {
        $hasOneValuePerChannel = $this->attributeHasOneValuePerChannel->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );
        $validator = Validation::createValidator();

        return $validator->validate(
            $hasOneValuePerChannel,
            new Callback(function ($hasOneValuePerChannel, ExecutionContextInterface $context) use ($attributeCode) {
                if ($hasOneValuePerChannel) {
                    $context
                        ->buildViolation(
                            ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_SHOULD_NOT_HAVE_ONE_VALUE_PER_CHANNEL,
                            ['%attribute_code%' => $attributeCode]
                        )
                        ->addViolation();
                }
            })
        );
    }

    private function checkHasNotOneValuePerLocale(
        string $assetFamilyIdentifier,
        string $attributeCode
    ): ConstraintViolationListInterface {
        $hasOneValuePerLocale = $this->attributeHasOneValuePerLocale->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );
        $validator = Validation::createValidator();

        return $validator->validate(
            $hasOneValuePerLocale,
            new Callback(function ($hasOneValuePerLocale, ExecutionContextInterface $context) use ($attributeCode) {
                if ($hasOneValuePerLocale) {
                    $context
                        ->buildViolation(
                            ProductLinkRulesShouldBeExecutable::EXTRAPOLATED_ATTRIBUTE_SHOULD_NOT_HAVE_ONE_VALUE_PER_LOCALE,
                            ['%attribute_code%' => $attributeCode]
                        )
                        ->addViolation();
                }
            })
        );
    }

    private function validateAttributeCode(string $attributeCode): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate(
            $attributeCode,
            [
                new NotBlank(),
                new Type(['type' => 'string']),
                new Length(['max' => AttributeCode::MAX_LENGTH, 'min' => 1]),
                new Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => Code::MESSAGE_WRONG_PATTERN,
                    ]
                ),
            ]
        );
    }
}
