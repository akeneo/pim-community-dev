<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PimEnterprise\Bundle\EnrichBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;

/**
 * Filter used to remove non standard properties from data sent by the UI.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class EntityWithPermissionsEditFilter implements CollectionFilterInterface
{
    /** @var array */
    private $propertiesToExclude;

    /**
     * @param string[] $propertiesToExclude
     */
    public function __construct(array $propertiesToExclude)
    {
        $this->propertiesToExclude = $propertiesToExclude;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = []): array
    {
        return array_diff_key(
            $collection,
            array_flip($this->propertiesToExclude)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = []): bool
    {
        return is_array($collection);
    }
}
