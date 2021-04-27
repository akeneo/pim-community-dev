<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;

class ColumnSorter implements ColumnSorterInterface
{
    /**
     * @param array<array> $columns
     * @param array<string> $context
     * @return array<array>
     */
    public function sort(array $columns, array $context = []): array
    {
        return $columns;
    }
}
