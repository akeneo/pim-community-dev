<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Comparator;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

class TableValueComparator implements ComparatorInterface
{
    public function supports($type): bool
    {
        return AttributeTypes::TABLE === $type;
    }

    public function compare($data, $originals): ?array
    {
        $default = ['locale' => null, 'scope' => null, 'data' => []];
        $originals = array_merge($default, $originals);

        if ($data['data'] === $originals['data']) {
            return null;
        }

        return $data;
    }
}
