<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\OptionValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ChannelShouldExist;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingConcatenateFields;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidAttribute;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\LocaleShouldBeActive;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExistingConcatenateFieldsValidator extends ConstraintValidator
{
    const VALID_TARGET_TYPES = [
        AttributeTypes::TEXT,
        AttributeTypes::TEXTAREA,
    ];

    /** @var ValueStringifierRegistry */
    private $valueStringifierRegistry;

    /** @var SetterRegistryInterface */
    private $setterRegistry;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        ValueStringifierRegistry $valueStringifierRegistry,
        SetterRegistryInterface $setterRegistry,
        GetAttributes $getAttributes
    ) {
        $this->valueStringifierRegistry = $valueStringifierRegistry;
        $this->setterRegistry = $setterRegistry;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        Assert::isInstanceOf($action, ProductConcatenateActionInterface::class, sprintf(
            'Action of type "%s" cannot be validated.',
            gettype($action)
        ));
        Assert::isInstanceOf($constraint, ExistingConcatenateFields::class, sprintf(
            'Constraint must be an instance of "%s".',
            ExistingConcatenateFields::class
        ));

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        /** @var ProductSource $source */
        foreach ($action->getSourceCollection() as $key => $source) {
            $basePath = sprintf('from[%d]', $key);
            $this->validateAttributeField(
                $validator,
                $source->getField(),
                $source->getLocale(),
                $source->getScope(),
                $basePath
            );

            $labelLocale = $source->getOptions()[OptionValueStringifier::LABEL_LOCALE_KEY] ?? null;
            if (null !== $labelLocale) {
                $validator->atPath($basePath . '.label_locale')->validate($labelLocale, new LocaleShouldBeActive());
            }

            $this->validateStringifierForSource($source->getField(), $constraint);
        }

        $targetField = $action->getTarget()->getField();
        $targetAttribute = $this->getAttributes->forCode($targetField);
        if (null !== $targetAttribute && !in_array($targetAttribute->type(), static::VALID_TARGET_TYPES)) {
            $this->context->buildViolation($constraint->messageErrorTarget, ['%field%' => $targetField])
                ->addViolation();

            return;
        }

        $this->validateSetterForTarget($targetField, $constraint);
    }

    private function validateStringifierForSource(string $sourceField, ExistingConcatenateFields $constraint): void
    {
        $attribute = $this->getAttributes->forCode($sourceField);
        if (null !== $attribute && null === $this->valueStringifierRegistry->getStringifier($attribute->type())) {
            $this->context->buildViolation($constraint->messageErrorSource, ['%field%' => $sourceField])
                ->addViolation();
        }
    }

    private function validateSetterForTarget(string $targetField, ExistingConcatenateFields $constraint): void
    {
        $setter = $this->setterRegistry->getSetter($targetField);
        if (null === $setter) {
            $this->context->buildViolation($constraint->messageErrorTarget, ['%field%' => $targetField])
                ->addViolation();
        }
    }

    private function validateAttributeField(
        ContextualValidatorInterface $validator,
        ?string $attributeCode,
        ?string $locale,
        ?string $scope,
        string $basePath
    ): void {
        $validator->atPath($basePath . '.field')->validate($attributeCode, new IsValidAttribute([
            'locale' => $locale,
            'scope' => $scope,
            'errorOnAttributeNotFound' => true,
        ]));
        $validator->atPath($basePath . '.locale')->validate($locale, new LocaleShouldBeActive());
        $validator->atPath($basePath . '.scope')->validate($scope, new ChannelShouldExist());
    }
}
