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
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CalculateActionFields;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CalculateActionFieldsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CalculateActionFields) {
            throw new UnexpectedTypeException($constraint, CalculateActionFields::class);
        }
        if (!$value instanceof ProductCalculateAction) {
            throw new UnexpectedTypeException($value, ProductCalculateAction::class);
        }

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $validator->atPath('destination.field')->validate(
            $value->getDestination()->getField(),
            new AttributeShouldBeNumeric()
        );
        $validator->atPath('source.field')->validate(
            $value->getSource()->getAttributeCode(),
            new AttributeShouldBeNumeric()
        );

        // validate operation field
        foreach ($value->getOperationList() as $index => $operation) {
            $path = sprintf('operation_list[%d].field', $index);
            $validator->atPath($path)->validate(
                $operation->getOperand()->getAttributeCode(),
                new AttributeShouldBeNumeric()
            );
        }
    }
}
