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

    public function __construct(
        GetAttributes $getAttributes,
        ChannelExistsWithLocaleInterface $channelExistsWithLocale
    ) {
        $this->getAttributes = $getAttributes;
        $this->channelExistsWithLocale = $channelExistsWithLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attributeCode, Constraint $constraint)
    {
        if (null === $attributeCode || !is_string($attributeCode)) {
            return;
        }
        Assert::isInstanceOf($constraint, IsValidAttribute::class, sprintf(
            'Constraint must be an instance of "%s".',
            IsValidAttribute::class
        ));

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            $this->context->buildViolation($constraint->messageAttributeNotFound, ['%field%' => $attributeCode])
                ->addViolation();

            return;
        }

        if ($attribute->isLocalizable() && null === $constraint->getLocale()) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is localizable and no locale is provided', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isLocalizable() && null !== $constraint->getLocale()) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not localizable and a locale is provided', $attribute->code()))
                ->addViolation();
        }

        if ($attribute->isScopable() && null === $constraint->getScope()) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is scopable and no channel is provided', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isScopable() && null !== $constraint->getScope()) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not scopable and a channel is provided', $attribute->code()))
                ->addViolation();
        }

        $this->checkLocaleSpecific($attribute, $constraint);
        $this->checkLocaleIsBoundToChannel($attribute, $constraint);
    }

    private function checkLocaleSpecific(Attribute $attribute, IsValidAttribute $constraint): void
    {
        if ($attribute->isLocalizable()
            && $attribute->isLocaleSpecific()
            && null !== $constraint->getLocale()
            && !in_array($constraint->getLocale(), $attribute->availableLocaleCodes())
        ) {
            $this->context
                ->buildViolation(sprintf(
                    'The "%s" locale code is not available for the "%s" locale specific attribute code',
                    $constraint->getLocale(),
                    $attribute->code()
                ))
                ->addViolation();
        }
    }

    private function checkLocaleIsBoundToChannel(Attribute $attribute, IsValidAttribute $constraint): void
    {
        if ($attribute->isLocalizableAndScopable()
            && null !== $constraint->getLocale()
            && null !== $constraint->getScope()
            && !$this->channelExistsWithLocale->isLocaleBoundToChannel($constraint->getLocale(), $constraint->getScope())
        ) {
            $this->context
                ->buildViolation(sprintf(
                    'The "%s" locale code is not bound to the "%s" channel code',
                    $constraint->getLocale(),
                    $constraint->getScope()
                ))
                ->addViolation();
        }
    }
}
