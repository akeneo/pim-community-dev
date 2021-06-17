<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
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
class ProductAssignmentsValidator
{
    private const CHANNEL_CODE = 'channel';
    private const LOCALE_FIELD = 'locale';
    private const MODE_FIELD = 'mode';

    private RuleEngineValidatorACLInterface $ruleEngineValidatorACL;

    private ExtrapolatedAttributeValidator $extrapolatedAttributeValidator;

    private ChannelAndLocaleValidator $channelAndLocaleValidator;

    private GetAssetCollectionTypeAdapterInterface $findAssetCollectionTypeACL;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        ExtrapolatedAttributeValidator $extrapolatedAttributeValidator,
        ChannelAndLocaleValidator $channelAndLocaleValidator,
        GetAssetCollectionTypeAdapterInterface $findAssetCollectionTypeACL
    ) {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->extrapolatedAttributeValidator = $extrapolatedAttributeValidator;
        $this->channelAndLocaleValidator = $channelAndLocaleValidator;
        $this->findAssetCollectionTypeACL = $findAssetCollectionTypeACL;
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
        $violations = $this->checkChannel($productAssignment);
        $violations->addAll($this->checkLocale($productAssignment));
        $modeViolations = $this->checkMode($productAssignment);
        $violations->addAll($modeViolations);

        if ($this->hasAnyExtrapolation($productAssignment)) {
            $violations->addAll($this->checkExtrapolatedAttributes($productAssignment, $assetFamilyIdentifier));

            return $violations;
        }

        if ($modeViolations->count() === 0) {
            $ruleEngineValidations = $this->ruleEngineValidatorACL->validateProductAssignment($productAssignment);
            $violations->addAll($ruleEngineValidations);
            if ($ruleEngineValidations->count() === 0) {
                $violations->addAll($this->checkProductAttributeReferencesThisAssetFamily($productAssignment, $assetFamilyIdentifier));
            }
        }

        return $violations;
    }

    private function checkChannel(array $productAssignment): ConstraintViolationListInterface
    {
        $channelCode = $productAssignment[self::CHANNEL_CODE] ?? null;

        return $this->channelAndLocaleValidator->checkChannelExistsIfAny($channelCode);
    }

    private function checkLocale(array $productAssignment): ConstraintViolationListInterface
    {
        $localeCode = $productAssignment[self::LOCALE_FIELD] ?? null;

        return $this->channelAndLocaleValidator->checkLocaleExistsIfAny($localeCode);
    }

    private function hasAnyExtrapolation(array $productAssignment): bool
    {
        $isFieldExtrapolated = ReplacePattern::isExtrapolation($productAssignment['attribute']);
        $isLocaleExtrapolated = isset($productAssignment[self::LOCALE_FIELD]) ? ReplacePattern::isExtrapolation($productAssignment[self::LOCALE_FIELD]) : false;
        $isChannelExtrapolated = isset($productAssignment[self::CHANNEL_CODE]) ? ReplacePattern::isExtrapolation($productAssignment[self::CHANNEL_CODE]) : false;

        return $isFieldExtrapolated || $isLocaleExtrapolated || $isChannelExtrapolated;
    }

    private function checkExtrapolatedAttributes(array $productAssignment, string $assetFamilyIdentifier): ConstraintViolationListInterface
    {
        $violations = $this->extrapolatedAttributeValidator->checkAttribute(
            $productAssignment['attribute'],
            $assetFamilyIdentifier,
            [TextAttribute::ATTRIBUTE_TYPE]
        );

        if (isset($productAssignment[self::CHANNEL_CODE])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttribute(
                $productAssignment[self::CHANNEL_CODE],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        if (isset($productAssignment[self::LOCALE_FIELD])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttribute(
                $productAssignment[self::LOCALE_FIELD],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }

        return $violations;
    }

    private function checkNotEmpty(array $productAssignments): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate($productAssignments,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]
        );
    }

    private function checkMode(array $productAssignment): ConstraintViolationListInterface
    {
        $allowedModes = Action::ALLOWED_MODES;
        $validator = Validation::createValidator();

        return $validator->validate(
            $productAssignment[self::MODE_FIELD],
            new Callback(function ($actualMode, ExecutionContextInterface $context) use ($allowedModes) {
                if (!in_array($actualMode, $allowedModes)) {
                    $context
                        ->buildViolation(
                            ProductLinkRulesShouldBeExecutable::ASSIGNMENT_MODE_NOT_SUPPORTED,
                            ['%assignment_mode%' => $actualMode]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }

    private function checkProductAttributeReferencesThisAssetFamily(
        array $productAssignment,
        string $expectedAssetFamilyIdentifier
    ): ConstraintViolationListInterface {
        $validator = Validation::createValidator();

        return $validator->validate(
            $productAssignment['attribute'],
            new Callback(function ($productAttributeCode, ExecutionContextInterface $context) use ($expectedAssetFamilyIdentifier) {
                try {
                    $actualAssetFamilyIdentifier = $this->findAssetCollectionTypeACL->fetch($productAttributeCode);
                    if ($expectedAssetFamilyIdentifier !== $actualAssetFamilyIdentifier) {
                        $context
                            ->buildViolation(
                                ProductLinkRulesShouldBeExecutable::ASSIGNMENT_ATTRIBUTE_DOES_NOT_SUPPORT_THIS_ASSET_FAMILY,
                                [
                                    '%product_attribute_code%'  => $productAttributeCode,
                                    '%asset_family_identifier%' => $expectedAssetFamilyIdentifier,
                                ]
                            )
                            ->addViolation();
                    }
                } catch (ProductAttributeCannotContainAssetsException $exception) {
                    $context->buildViolation(
                        ProductLinkRulesShouldBeExecutable::ASSIGNMENT_ATTRIBUTE_IS_NOT_AN_ASSET_COLLECTION,
                        ['%product_attribute_code%' => $productAttributeCode]
                    )->addViolation();
                } catch (ProductAttributeDoesNotExistException $exception) {
                    $context->buildViolation(
                        ProductLinkRulesShouldBeExecutable::ASSIGNMENT_ATTRIBUTE_DOES_NOT_EXISTS,
                        ['%product_attribute_code%' => $productAttributeCode]
                    )->addViolation();
                }
            }
            )
        );
    }
}
