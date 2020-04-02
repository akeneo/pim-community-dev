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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingClearFields;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExistingClearFieldsValidator extends ConstraintValidator
{
    /** @var ClearerRegistryInterface */
    private $clearerRegistry;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(ClearerRegistryInterface $clearerRegistry, GetAttributes $getAttributes)
    {
        $this->clearerRegistry = $clearerRegistry;
        $this->getAttributes = $getAttributes;
    }

    public function validate($action, Constraint $constraint): void
    {
        Assert::isInstanceOf($action, ProductClearActionInterface::class);
        Assert::isInstanceOf($constraint, ExistingClearFields::class);

        $clearer = $this->clearerRegistry->getClearer($action->getField());

        if (null === $clearer) {
            $this->context->buildViolation($constraint->messageError, ['%field%' => $action->getField()])
                ->addViolation();

            return;
        }

        $attribute = $this->getAttributes->forCode($action->getField());
        if (null !== $attribute) {
            $this->checkAttribute($attribute, $action->getLocale(), $action->getScope());
        }
    }

    private function checkAttribute(
        Attribute $attribute,
        ?string $locale,
        ?string $channel
    ): void {
        if ($attribute->isLocalizable() && null === $locale) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is localizable and no locale is provided.', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isLocalizable() && null !== $locale) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not localizable and a locale is provided.', $attribute->code()))
                ->addViolation();
        }

        if ($attribute->isScopable() && null === $channel) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is scopable and no channel is provided.', $attribute->code()))
                ->addViolation();
        } elseif (!$attribute->isScopable() && null !== $channel) {
            $this->context
                ->buildViolation(sprintf('The "%s" attribute code is not scopable and a channel is provided.', $attribute->code()))
                ->addViolation();
        }
    }
}
