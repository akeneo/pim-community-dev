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

class ReadColumns extends Constraint
{
    public const UNIQUE_IDENTIFIER_AFTER_FIRST_COLUMN = 'akeneo.tailored_import.validation.file_structure.unique_identifier_column_should_be_after_first_column_message';
    public const AT_LEAST_ONE_COLUMN = 'akeneo.tailored_import.validation.columns.at_least_one_required';
    public const MAX_COUNT_REACHED = 'akeneo.tailored_import.validation.columns.max_count_reached';
    public const MISSING_QUERY_PARAMS = 'akeneo.tailored_import.validation.missing_query_params';
    public const EMPTY_HEADER = 'akeneo.tailored_import.validation.file_structure.header_row_should_not_contain_empty_cell';
}
