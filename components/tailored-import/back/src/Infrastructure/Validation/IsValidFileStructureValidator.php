<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidFileStructureValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsValidFileStructure) {
            throw new \LogicException('The constraint should be an instance of IsValidFileStructure.');
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->atPath('[file_structure]')->validate($value, new Collection([
            'fields' => [
                'header_row' => [
                    new Type('int'),
                    new NotBlank(),
                    new Range(['min' => 1, 'max' => 500]),
                ],
                'first_column' => [
                    new Type('int'),
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 500]),
                ],
                'first_product_row' => [
                    new Type('int'),
                    new NotBlank(),
                    new Range(['min' => 2, 'max' => 500]),
                ],
                'unique_identifier_column' => [
                    new Type('int'),
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 500]),
                ],
                'sheet_name' => [
                    new Type('string'),
                    new NotBlank(['allowNull' => true]),
                ],
            ],
        ]));

        if ($validator->getViolations()->count() > 0){
            return;
        }

        $validator->atPath('[file_structure][first_product_row]')->validate($value['first_product_row'], new Range(['min' => $value['header_row'], 'minMessage' => $constraint->firstProductRowShouldBeAfterHeaderRow]));
        $validator->atPath('[file_structure][unique_identifier_column]')->validate($value['unique_identifier_column'], new Range(['min' => $value['first_column'], 'minMessage' => $constraint->uniqueIdentifierColumnShouldBeAfterFirstColumnMessage]));
    }
}
