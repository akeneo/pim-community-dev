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
        Assert::isInstanceOf($constraint, IsValidAttribute::class, sprintf(
            'Constraint must be an instance of "%s".',
            IsValidAttribute::class
        ));
        $attributeCode = $this->propertyAccessor->getValue($object, $constraint->attributeProperty);
        if (null === $attributeCode) {
            return;
        }

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            return;
        }

        $localeCode = $this->propertyAccessor->getValue($object, $constraint->localeProperty);
        if ($attribute->isLocalizable() && null === $localeCode) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is localizable and no locale is provided', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isLocalizable() && null !== $localeCode) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not localizable and a locale is provided', $attribute->code()))
                ->addViolation();
        }

        $channelCode = $this->propertyAccessor->getValue($object, $constraint->channelProperty);
        if ($attribute->isScopable() && null === $channelCode) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is scopable and no channel is provided', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isScopable() && null !== $channelCode) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not scopable and a channel is provided', $attribute->code()))
                ->addViolation();
        }

        $this->checkLocaleSpecific($attribute, $localeCode);
        $this->checkLocaleIsBoundToChannel($attribute, $channelCode, $localeCode);
    }

    private function checkLocaleSpecific(Attribute $attribute, ?string $localeCode): void
    {
        if ($attribute->isLocalizable()
            && $attribute->isLocaleSpecific()
            && null !== $localeCode
            && !in_array($localeCode, $attribute->availableLocaleCodes())
        ) {
            $this->context
                ->buildViolation(sprintf(
                    'The "%s" locale code is not available for the "%s" locale specific attribute code',
                    $localeCode,
                    $attribute->code()
                ))
                ->addViolation();
        }
    }

    private function checkLocaleIsBoundToChannel(Attribute $attribute, ?string $channelCode, ?string $localeCode): void
    {
        if ($attribute->isLocalizableAndScopable()
            && null !== $localeCode
            && null !== $channelCode
            && !$this->channelExistsWithLocale->isLocaleBoundToChannel($localeCode, $channelCode)
        ) {
            $this->context
                ->buildViolation(sprintf(
                    'The "%s" locale code is not bound to the "%s" channel code',
                    $localeCode,
                    $channelCode
                ))
                ->addViolation();
        }
    }
}
