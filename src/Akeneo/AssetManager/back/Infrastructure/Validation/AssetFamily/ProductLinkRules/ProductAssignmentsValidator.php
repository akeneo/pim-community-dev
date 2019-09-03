<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
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
    private const CHANNEL_CODE = 'channel';
    private const LOCALE_FIELD = 'locale';

    /** @var RuleEngineValidatorACLInterface */
    private $ruleEngineValidatorACL;

    /** @var ExtrapolatedAttributeValidator */
    private $extrapolatedAttributeValidator;

    /** @var ChannelAndLocaleValidator */
    private $channelAndLocaleValidator;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        ExtrapolatedAttributeValidator $extrapolatedAttributeValidator,
        ChannelAndLocaleValidator $channelAndLocaleValidator
    ) {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->extrapolatedAttributeValidator = $extrapolatedAttributeValidator;
        $this->channelAndLocaleValidator = $channelAndLocaleValidator;
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

        $violations = $this->channelAndLocaleValidator->checkChannelExistsIfAny($productAssignment[self::CHANNEL_CODE] ?? null);
        $violations->addAll($this->channelAndLocaleValidator->checkLocaleExistsIfAny($productAssignment[self::LOCALE_FIELD] ?? null));
        $violations->addAll($this->ruleEngineValidatorACL->validateProductAssignment($productAssignment));
        $violations->addAll($this->checkMode($productAssignment));

        return $violations;
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
        $ruleEngineViolations = $validator->validate($productAssignments,
            [new NotBlank(['message' => ProductLinkRulesShouldBeExecutable::PRODUCT_ASSIGNMENT_CANNOT_BE_EMPTY])]
        );

        return $ruleEngineViolations;
    }

    private function checkMode(array $productAssignment): ConstraintViolationListInterface
    {
        $allowedModes = Action::ALLOWED_MODES;
        $validator = Validation::createValidator();
        $result = $validator->validate(
            $productAssignment['mode'],
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

        return $result;
    }
}
