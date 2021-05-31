<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ProductSelectionsValidator
{
    private const CHANNEL_FIELD = 'channel';
    private const LOCALE_FIELD = 'locale';
    private const FIELD_FIELD = 'field';
    private const FIELDS_WITH_NO_CHANNEL_NOR_LOCALES = ['enable', 'family', 'categories'];

    private RuleEngineValidatorACLInterface $ruleEngineValidatorACL;

    private ExtrapolatedAttributeValidator $extrapolatedAttributeValidator;

    private ChannelAndLocaleValidator $channelAndLocaleValidator;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        ExtrapolatedAttributeValidator $extrapolatedAttributeValidator,
        ChannelAndLocaleValidator $channelAndLocaleValidator
    ) {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->extrapolatedAttributeValidator = $extrapolatedAttributeValidator;
        $this->channelAndLocaleValidator = $channelAndLocaleValidator;
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

        return $validator->validate($productSelections,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_SELECTION_CANNOT_BE_EMPTY])]
        );
    }

    private function validateProductSelection(
        array $productSelection,
        string $assetFamilyIdentifier
    ): ConstraintViolationListInterface {
        $violations = $this->checkProductField($productSelection);
        $violations->addAll($this->checkChannel($productSelection));
        $violations->addAll($this->checkLocale($productSelection));

        if ($this->hasAnyExtrapolation($productSelection)) {
            return $this->checkExtrapolatedAttributes($productSelection, $assetFamilyIdentifier);
        }
        $violations->addAll($this->ruleEngineValidatorACL->validateProductSelection($productSelection));

        return $violations;
    }

    private function hasAnyExtrapolation(array $productSelection): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productSelection[self::FIELD_FIELD]);
        $isValueExtrapolated = ReplacePattern::isExtrapolation($productSelection['value']);
        $isLocaleExtrapolated = isset($productSelection[self::LOCALE_FIELD])
            ? ReplacePattern::isExtrapolation($productSelection[self::LOCALE_FIELD]) : false;
        $isChannelExtrapolated = isset($productSelection[self::CHANNEL_FIELD])
            ? ReplacePattern::isExtrapolation($productSelection[self::CHANNEL_FIELD]) : false;

        return $isFieldExtrapolated || $isValueExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(
        array $productSelection,
        string $assetFamilyIdentifier
    ): ConstraintViolationListInterface {
        $violations = $this->extrapolatedAttributeValidator->checkAttribute(
            $productSelection[self::FIELD_FIELD],
            $assetFamilyIdentifier,
            [TextAttribute::ATTRIBUTE_TYPE]
        );
        $violations->addAll($this->extrapolatedAttributeValidator->checkAttribute(
            $productSelection['value'],
            $assetFamilyIdentifier,
            [
                TextAttribute::ATTRIBUTE_TYPE,
                OptionAttribute::ATTRIBUTE_TYPE,
                OptionCollectionAttribute::ATTRIBUTE_TYPE
            ]
        ));
        if (isset($productSelection[self::CHANNEL_FIELD])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttribute(
                $productSelection[self::CHANNEL_FIELD],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        if (isset($productSelection[self::LOCALE_FIELD])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttribute(
                $productSelection[self::LOCALE_FIELD],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }

        return $violations;
    }

    private function checkChannel(array $productSelection): ConstraintViolationListInterface
    {
        $channelCode = $productSelection[self::CHANNEL_FIELD] ?? null;

        return $this->channelAndLocaleValidator->checkChannelExistsIfAny($channelCode);
    }

    private function checkLocale(array $productSelection): ConstraintViolationListInterface
    {
        $localeCode = $productSelection[self::LOCALE_FIELD] ?? null;

        return $this->channelAndLocaleValidator->checkLocaleExistsIfAny($localeCode);
    }

    private function checkProductField(array $productSelection): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate(
            $productSelection,
            new Callback(function ($productSelection, ExecutionContextInterface $context) {
                $productField = $productSelection[self::FIELD_FIELD];
                if (!in_array($productField, self::FIELDS_WITH_NO_CHANNEL_NOR_LOCALES)) {
                    return;
                }

                if (!empty($productSelection[self::CHANNEL_FIELD])) {
                    $context->buildViolation(
                        ProductLinkRulesShouldBeExecutable::CHANNEL_NOT_SUPPORTED_FOR_FIELD,
                        ['%product_field%' => $productField]
                    )->addViolation();
                }
                if (!empty($productSelection[self::LOCALE_FIELD])) {
                    $context->buildViolation(
                        ProductLinkRulesShouldBeExecutable::LOCALE_NOT_SUPPORTED_FOR_FIELD,
                        ['%product_field%' => $productField]
                    )->addViolation();
                }
            }
            )
        );
    }
}
