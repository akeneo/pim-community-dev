<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class LinkedProductAttribute
{
    public function __construct(
        private string $code,
        private string $type,
        private array $labels,
        private string $referenceDataName,
        private bool $useableAsGridFilter
    ) {
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'labels' => $this->labels,
            'reference_data_name' => $this->referenceDataName,
            'useable_as_grid_filter' => $this->useableAsGridFilter,
        ];
    }
}
