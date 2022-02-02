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
    public const MAX_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.max_count_reached';
    public const UUID_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.data_mappings.uuid.should_be_unique';
    public const NO_IDENTIFIER_TARGET_FOUND = 'akeneo.tailored_import.validation.data_mappings.no_identifier_target_found';
    public const TOO_MANY_IDENTIFIER_TARGET_FOUND = 'akeneo.tailored_import.validation.data_mappings.too_many_identifier_target_found';
    public const MIN_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.sources.min_count_reached';
    public const MAX_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached';
    public const SOURCES_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique';

    public function validatedBy(): string
    {
        return 'akeneo.tailored_import.validation.data_mappings';
    }
}
