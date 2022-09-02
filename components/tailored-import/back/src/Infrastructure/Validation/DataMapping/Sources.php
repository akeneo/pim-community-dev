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

final class Sources extends Constraint
{
    public const AT_LEAST_ONE_REQUIRED = 'akeneo.tailored_import.validation.data_mappings.sources.at_least_one_required';
    public const MAX_SOURCES_COUNT_REACHED = 'akeneo.tailored_import.validation.data_mappings.sources.max_count_reached';
    public const SOURCES_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.data_mappings.sources.should_be_unique';
    public const SOURCES_SHOULD_EXIST = 'akeneo.tailored_import.validation.data_mappings.sources.should_exist';

    public function __construct(
        private bool $supportsMultiSource,
        private array $columnsUuids,
    ) {
        parent::__construct();
    }

    public function supportsMultiSource(): bool
    {
        return $this->supportsMultiSource;
    }

    public function getColumnUuids(): array
    {
        return $this->columnsUuids;
    }
}
