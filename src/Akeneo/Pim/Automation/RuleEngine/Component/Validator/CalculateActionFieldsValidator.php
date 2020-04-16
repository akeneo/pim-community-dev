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

use Akeneo\Channel\Component\Validator\Constraint\ConversionUnits;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ActiveCurrency;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CalculateActionFields;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ChannelShouldExist;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IsValidAttribute;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\LocaleShouldBeActive;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Webmozart\Assert\Assert;

class CalculateActionFieldsValidator extends ConstraintValidator
{
    public function validate($action, Constraint $constraint)
    {
        Assert::isInstanceOf($action, ProductCalculateActionInterface::class, sprintf(
            'Action of type "%s" cannot be validated.',
            gettype($action)
        ));
        Assert::isInstanceOf($constraint, CalculateActionFields::class, sprintf(
            'Constraint must be an instance of "%s".',
            CalculateActionFields::class
        ));

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $destination = $action->getDestination();
        $this->validateAttributeConsistency(
            $validator,
            'destination',
            $destination->getField(),
            $destination->getScope(),
            $destination->getLocale(),
            $destination->getCurrency()
        );
        if (null !== $destination->getUnit()) {
            $validator->atPath('destination.unit')->validate(
                [$destination->getField() => $destination->getUnit()],
                new ConversionUnits()
            );
        }

        $source = $action->getSource();
        $this->validateAttributeConsistency(
            $validator,
            'source',
            $source->getAttributeCode(),
            $source->getChannelCode(),
            $source->getLocaleCode(),
            $source->getCurrencyCode()
        );

        foreach ($action->getOperationList() as $index => $operation) {
            $path = sprintf('operation_list[%d]', $index);
            $this->validateAttributeConsistency(
                $validator,
                $path,
                $operation->getOperand()->getAttributeCode(),
                $operation->getOperand()->getChannelCode(),
                $operation->getOperand()->getLocaleCode(),
                $operation->getOperand()->getCurrencyCode()
            );
        }
    }

    private function validateAttributeConsistency(
        ContextualValidatorInterface $validator,
        string $path,
        ?string $attributeCode,
        ?string $scope,
        ?string $locale,
        ?string $currency
    ): void {
        $validator->atPath(sprintf('%s.field', $path))->validate(
            $attributeCode,
            [
                new IsValidAttribute(['scope' => $scope, 'locale' => $locale]),
                new AttributeShouldBeNumeric()
            ]
        );
        $validator->atPath(sprintf('%s.scope', $path))->validate(
            $scope,
            new ChannelShouldExist()
        );
        $validator->atPath(sprintf('%s.locale', $path))->validate(
            $locale,
            new LocaleShouldBeActive()
        );
        $validator->atPath(sprintf('%s.currency', $path))->validate(
            $currency,
            new ActiveCurrency(['attributeCode' => $attributeCode])
        );
    }
}
