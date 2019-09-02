<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\ReplacePattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
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

    /** @var ExtrapolatedAttributeValidator */
    private $extrapolatedAttributeValidator;

    /** @var ChannelExistsInterface */
    private $channelExists;

    public function __construct(
        RuleEngineValidatorACLInterface $ruleEngineValidatorACL,
        ExtrapolatedAttributeValidator $extrapolatedAttributeValidator,
        ChannelExistsInterface $channelExists
    ) {
        $this->ruleEngineValidatorACL = $ruleEngineValidatorACL;
        $this->extrapolatedAttributeValidator = $extrapolatedAttributeValidator;
        $this->channelExists = $channelExists;
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
        $violations = $this->checkChannelExistsIfAny($productSelection);
        $violations->addAll($this->ruleEngineValidatorACL->validateProductSelection($productSelection));

        return $violations;
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
        $violations = $this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
            $productSelection['field'],
            $assetFamilyIdentifier,
            [TextAttribute::ATTRIBUTE_TYPE]
        );
        $violations->addAll($this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
            $productSelection['value'],
            $assetFamilyIdentifier,
            [
                TextAttribute::ATTRIBUTE_TYPE,
                OptionAttribute::ATTRIBUTE_TYPE,
                OptionCollectionAttribute::ATTRIBUTE_TYPE
            ]
        ));
        if (isset($productSelection['channel'])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
                $productSelection['channel'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }
        if (isset($productSelection['locale'])) {
            $violations->addAll($this->extrapolatedAttributeValidator->checkAttributeExistsAndHasASupportedType(
                $productSelection['locale'],
                $assetFamilyIdentifier,
                [TextAttribute::ATTRIBUTE_TYPE]
            ));
        }

        return $violations;
    }

    private function checkChannelExistsIfAny(array $productSelection): ConstraintViolationListInterface
    {
        if (!isset($productSelection['channel'])) {
            return new ConstraintViolationList();
        }
        $channelCode = $productSelection['channel'];
        $isChannelExisting = $this->channelExists->exists(ChannelIdentifier::fromCode($channelCode));

        $validator = Validation::createValidator();
        return $validator->validate(
            $isChannelExisting,
            new Callback(function ($attributeExists, ExecutionContextInterface $context) use (
                $channelCode
            ) {
                if (!$attributeExists) {
                    $context
                        ->buildViolation(ProductLinkRulesShouldBeExecutable::CHANNEL_SHOULD_EXISTS,
                            ['%channel_code%' => $channelCode]
                        )
                        ->addViolation();
                }
            }
            )
        );
    }
}
