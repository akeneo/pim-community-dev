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

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class IsValidAttributeValidator extends ConstraintValidator
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var ChannelExistsWithLocaleInterface */
    private $channelExistsWithLocale;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->getAttributes = $getAttributes;
        $this->channelExistsWithLocale = $channelExistsWithLocale;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        if (null === $object || !is_object($object)) {
            return;
        }
        Assert::isInstanceOf($constraint, IsValidAttribute::class);
        $attributeCode = $this->propertyAccessor->getValue($object, $constraint->attributeProperty);
        if (null === $attributeCode) {
            return;
        }

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            return;
        }

        $localeCode = $this->propertyAccessor->getValue($object, $constraint->localeProperty);
        $channelCode = $this->propertyAccessor->getValue($object, $constraint->channelProperty);

        $this->validateScope($attribute, $channelCode);
        $this->validateLocale($attribute, $localeCode, $channelCode);
    }

    /**
     * Check if locale data is consistent with the attribute localizable property
     */
    private function validateLocale(Attribute $attribute, $locale): void
    {
        if (!$attribute->isLocalizable() && null === $locale) {
            return;
        }

        if ($attribute->isLocalizable() && null === $locale) {
            $this->addViolation(sprintf(
                'Attribute "%s" expects a locale, none given.',
                $attribute->code()
            ));

            return;
        }

        if (!is_string($locale)) {
            return;
        }

        if (!$attribute->isLocalizable() && null !== $locale) {
            $this->addViolation(sprintf(
                'Attribute "%s" does not expect a locale, "%s" given.',
                $attribute->code(),
                $locale
            ));
        }

        if (is_string($locale) && $attribute->isLocaleSpecific() && !in_array($locale, $attribute->availableLocaleCodes())) {
            $this->addViolation(sprintf(
                'Attribute "%s" is locale specific and expects one of these locales: %s, "%s" given.',
                $attribute->code(),
                implode($attribute->availableLocaleCodes(), ', '),
                $locale
            ));
        }
    }

    /**
     * Check if scope data is consistent with the attribute scopable property
     */
    private function validateScope(Attribute $attribute, $scope): void
    {
        if (!$attribute->isScopable() && null === $scope) {
            return;
        }

        if ($attribute->isScopable() && null === $scope) {
            $this->addViolation(sprintf(
                'Attribute "%s" expects a scope, none given.',
                $attribute->code()
            ));

            return;
        }

        if (!is_string($scope)) {
            return;
        }

        if (!$attribute->isScopable() && null !== $scope) {
            $this->addViolation(sprintf(
                'Attribute "%s" does not expect a scope, "%s" given.',
                $attribute->code(),
                $scope
            ));

            return;
        }

        if (!$this->channelExistsWithLocale->doesChannelExist($scope)) {
            $this->addViolation(sprintf(
                'Attribute "%s" expects an existing scope, "%s" given.',
                $attribute->code(),
                $scope
            ));
        }
    }

    private function addViolation(string $message): void
    {
        $this->context->buildViolation($message)->addViolation();
    }
}
