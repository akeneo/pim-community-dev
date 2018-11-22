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

namespace Akeneo\Pim\Enrichment\Asset\Component\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetCollectionComparator implements ComparatorInterface
{
    /** @var array */
    protected $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
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
        $originals = array_merge($default, $originals);

        if ($data['data'] === $originals['data']) {
            return null;
        }

        return $data;
    }
}
