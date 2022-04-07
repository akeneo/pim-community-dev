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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping;

use Symfony\Component\Validator\Constraint;

class Sources extends Constraint
{
    public const MIN_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.sources.min_count_reached';
    public const MAX_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached';
    public const SOURCES_COUNT_MISMATCHED = 'akeneo.tailored_import.validation.data_mappings.sources.count_mismatched';
    public const SOURCES_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique';
    public const SOURCES_SHOULD_EXIST = 'akeneo.tailored_import.validation.data_mappings.sources.should_exist';

    public function __construct(
        private bool $supportsMultiSource,
        private array $columns,
    ) {
        parent::__construct();
    }

    public function supportsMultiSource(): bool
    {
        return $this->supportsMultiSource;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
