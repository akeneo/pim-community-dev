<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

final class RecordsComparator implements ComparatorInterface
{
    /**
     * @param array $types
     */
    public function __construct(private array $types)
    {
    }

    public function supports($type): bool
    {
        return in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals): ?array
    {
        $default = ['locale' => null, 'scope' => null, 'data' => []];
        $originals = \array_merge($default, $originals);

        if (null === $data['data'] || '' === $data['data']) {
            $data['data'] = [];
        }
        $data['data'] = \array_unique($data['data']);

        $originalsToLower = \array_map('strtolower', $originals['data']);
        $dataToLower = \array_unique(\array_map('strtolower', $data['data']));

        \sort($originalsToLower);
        \sort($dataToLower);

        if ($dataToLower === $originalsToLower) {
            return null;
        }

        return $data;
    }
}
