<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;

/**
 * Asset family collection type
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetCollectionType extends AbstractAttributeType
{
    public const ASSET_COLLECTION = 'pim_catalog_asset_collection';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::ASSET_COLLECTION;
    }
}
