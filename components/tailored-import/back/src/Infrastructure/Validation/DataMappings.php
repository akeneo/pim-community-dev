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

class DataMappings extends Constraint
{
    public const MAX_DATA_MAPPING_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.max_data_mapping_count_reached';
    public const NO_IDENTIFIER_DATA_MAPPING_FOUND = 'akeneo.tailored_import.validation.data_mappings.no_identifier_data_mapping_found';
    public const DATA_MAPPING_MIN_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.data_mapping_min_sources_count_reached';
    public const DATA_MAPPING_MAX_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.data_mapping_max_sources_count_reached';
}
