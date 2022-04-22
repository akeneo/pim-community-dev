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

use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsValidFileStructureValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsValidFileStructure) {
            throw new UnexpectedTypeException($constraint, IsValidFileStructure::class);
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->atPath('[file_structure]')->validate($value, new Collection([
            'fields' => [
                'header_row' => [
                    new Type('int'),
                    new NotBlank(),
                ],
                'first_column' => [
                    new Type('int'),
                    new NotBlank(),
                ],
                'first_product_row' => [
                    new Type('int'),
                    new NotBlank(),
                ],
                'unique_identifier_column' => [
                    new Type('int'),
                    new NotBlank(),
                ],
                'sheet_name' => [
                    new Type('string'),
                    new NotBlank(['allowNull' => true]),
                ],
            ],
        ]));

        if ($validator->getViolations()->count() > 0) {
            return;
        }

        $validator->atPath('[file_structure]')->validate($value, new Collection([
            'fields' => [
                'header_row' => new Range([
                    'min' => FileStructure::MINIMUM_HEADER_LINE,
                    'max' => FileStructure::MAXIMUM_HEADER_LINE,
                ]),
                'first_column' => new Range([
                    'min' => 0,
                    'max' => FileStructure::MAXIMUM_COLUMN_COUNT,
                ]),
                'first_product_row' => new Range([
                    'min' => $value['header_row'] + 1,
                    'minMessage' => IsValidFileStructure::FIRST_PRODUCT_ROW_SHOULD_BE_AFTER_HEADER_ROW,
                    'max' => FileStructure::MAXIMUM_FIRST_PRODUCT_LINE,
                ]),
                'unique_identifier_column' => new Range([
                    'min' => $value['first_column'],
                    'minMessage' => IsValidFileStructure::UNIQUE_IDENTIFIER_COLUMN_SHOULD_BE_AFTER_FIRST_COLUMN,
                    'max' => FileStructure::MAXIMUM_COLUMN_COUNT,
                ]),
            ],
            'allowExtraFields' => true,
        ]));
    }
}
