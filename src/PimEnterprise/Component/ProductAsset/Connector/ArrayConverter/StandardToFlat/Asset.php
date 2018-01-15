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
use Pim\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;

/**
 * Standard to flat array converter for asset
 *
 * Before:
 * [
 *      'code'        => 'mycode',
 *      'localizable' => false,
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
 *      'end_of_use'  => '2018-02-01T00:00:00+01:00',
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
 *
 * TODO: change naming "localized" in "localizable" in major version 3.0 (BC break)
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class Asset extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function convertProperty($field, $data, array $convertedItem, array $options)
    {
        switch ($field) {
            case 'code':
            case 'description':
                $convertedItem[$field] = (string) $data;
                break;
            case 'end_of_use':
                $datetime = \DateTime::createFromFormat(\DateTime::W3C, $data);
                $convertedItem[$field] = false !== $datetime ? $datetime->format('Y-m-d') : (string) $data;

                break;
            case 'localizable':
                $convertedItem['localized'] = (true === $data) ? '1' : '0';
                break;
            case 'tags':
            case 'categories':
                $convertedItem[$field] = implode(',', $data);
                break;
        }

        return $convertedItem;
    }
}
