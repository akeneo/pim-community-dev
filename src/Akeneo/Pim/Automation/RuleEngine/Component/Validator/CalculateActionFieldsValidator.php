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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CalculateActionFields;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingConcatenateFields;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
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

        $validator->atPath('destination.field')->validate(
            $action->getDestination()->getField(),
            new AttributeShouldBeNumeric()
        );
        $validator->atPath('source.field')->validate(
            $action->getSource()->getAttributeCode(),
            new AttributeShouldBeNumeric()
        );

        // validate operation field
        foreach ($action->getOperationList() as $index => $operation) {
            $path = sprintf('operation_list[%d].field', $index);
            $validator->atPath($path)->validate(
                $operation->getOperand()->getAttributeCode(),
                new AttributeShouldBeNumeric()
            );
        }
    }
}
