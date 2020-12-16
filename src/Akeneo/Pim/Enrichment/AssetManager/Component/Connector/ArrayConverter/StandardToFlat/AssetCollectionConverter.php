<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class AssetCollectionConverter extends AbstractValueConverter implements ValueConverterInterface
{
    private const FILE_PATH_PATTERN = '%s-file_path';

    public function __construct(AttributeColumnsResolver $columnsResolver)
    {
        parent::__construct($columnsResolver, [AssetCollectionType::ASSET_COLLECTION]);
    }

    /**
     * {@inheritdoc}
     *
     * Convert a standard asset collection product value to a flat one.
     *
     * Given a 'colors' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'ecommerce',
     *         'data'   => ['asset1', 'asset2'],
     *         'paths'  => ['folder1/file1.jpg', 'folder2/file2.jpg']
     *     ],
     * ]
     *
     * It will return:
     * [
     *     'colors-de_DE-ecommerce' => 'blue,yellow,red',
     *     'colors-de_DE-ecommerce-file_path' => 'folder1/file1.jpg,folder2/file2.jpg',
     * ]
     */
    public function convert($attributeCode, $data)
    {
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );

            $convertedItem[$flatName] = implode(',', $value['data']);

            // Add paths information
            if (isset($value['paths'])) {
                $flatName = sprintf(self::FILE_PATH_PATTERN, $flatName);
                $convertedItem[$flatName] = implode(',', $value['paths']);
            }
        }

        return $convertedItem;
    }
}
