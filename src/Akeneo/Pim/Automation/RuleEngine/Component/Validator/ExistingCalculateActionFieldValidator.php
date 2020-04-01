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
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingCalculateActionField;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExistingCalculateActionFieldValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExistingCalculateActionField) {
            throw new UnexpectedTypeException($constraint, ExistingCalculateActionField::class);
        }
        if (!$value instanceof ProductCalculateAction){
            throw new UnexpectedTypeException($value, ProductCalculateAction::class);
        }

        $validator = $this->context->getValidator();

        // validate destination field
        $this->buildViolations(
            $validator->validate($value->getDestination()->getField(), new AttributeShouldBeNumeric()),
            'destination.field'
        );

        // validate source field
        $this->buildViolations(
            $validator->validate($value->getSource()->getAttributeCode(), new AttributeShouldBeNumeric()),
            'source.field'
        );

        // validate operation field
        foreach ($value->getOperationList() as $index => $operation) {
            $this->buildViolations(
                $validator->validate($operation->getOperand()->getAttributeCode(), new AttributeShouldBeNumeric()),
                'operation_list[' .$index . '].field'
            );
        }

    }

    private function buildViolations(ConstraintViolationListInterface $violations, string $path): void
    {
        foreach ($violations as $violation) {
            $this->context
                ->buildViolation($violation->getMessage(), $violation->getParameters())
                ->atPath($path)
                ->addViolation();
        }
    }
}
