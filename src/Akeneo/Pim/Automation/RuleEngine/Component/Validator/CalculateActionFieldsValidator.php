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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
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
        $this->doValidate(
            $validator,
            'destination',
            $destination->getField(),
            $destination->getScope(),
            $destination->getLocale()
        );

        $source = $action->getSource();
        $this->doValidate(
            $validator,
            'source',
            $source->getAttributeCode(),
            $source->getChannelCode(),
            $source->getLocaleCode()
        );

        foreach ($action->getOperationList() as $index => $operation) {
            $path = sprintf('operation_list[%d]', $index);
            $this->doValidate(
                $validator,
                $path,
                $operation->getOperand()->getAttributeCode(),
                $operation->getOperand()->getChannelCode(),
                $operation->getOperand()->getLocaleCode()
            );
        }
    }

    private function doValidate(
        ContextualValidatorInterface $validator,
        string $path,
        ?string $attributeCode,
        ?string $scope,
        ?string $locale
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
        $validator->atPath(sprintf('%s.locale', $locale))->validate(
            $locale,
            new LocaleShouldBeActive()
        );
    }
}
