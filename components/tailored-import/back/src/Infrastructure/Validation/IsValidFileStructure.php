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

class IsValidFileStructure extends Constraint
{
    public string $firstProductRowShouldBeAfterHeaderRow = 'akeneo.tailored_import.validation.file_structure.first_product_row_should_be_after_header_row';
    public string $uniqueIdentifierColumnShouldBeAfterFirstColumnMessage = 'akeneo.tailored_import.validation.file_structure.unique_identifier_column_should_be_after_first_column_message';
}
