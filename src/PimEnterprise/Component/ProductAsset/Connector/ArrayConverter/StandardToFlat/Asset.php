<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Standard to flat array converter for asset
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class Asset implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts standard array to a flat one.
     *
     * Before:
     * [
     *      'code'        => 'mycode',
     *      'localized'   => false,
     *      'description' => 'My awesome description',
     *      'categories'  => [
     *          'myCat1',
     *          'myCat2',
     *          'myCat3',
     *      ],
     *      'tags'        => [
     *          'dog',
     *          'flowers',
     *          'cities',
     *          'animal',
     *          'sunset',
     *      ],
     *      'end_of_use'  => '2018-02-01',
     * ]
     *
     * After:
     * [
     *      'code'          => 'mycode',
     *      'localized'     => 0,
     *      'description'   => 'My awesome description',
     *      'categories'    => 'myCat1,myCat2,myCat3'
     *      'tags'          => 'dog,flowers,cities,animal,sunset',
     *      'end_of_use'    => '2018-02-01',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($field, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param mixed  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertField($field, $data, array $convertedItem)
    {
        switch ($field) {
            case 'code':
            case 'description':
            case 'end_of_use':
                $convertedItem[$field] = (string) $data;
                break;
            case 'localized':
                $convertedItem[$field] = (true === $data) ? '1' : '0';
                break;
            case 'tags':
            case 'categories':
                $convertedItem[$field] = implode(',', $data);
                break;
        }

        return $convertedItem;
    }
}
