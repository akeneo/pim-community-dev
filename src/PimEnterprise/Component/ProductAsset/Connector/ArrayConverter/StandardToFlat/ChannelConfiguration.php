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
 * Standard to flat array converter for channel asset configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ChannelConfiguration implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *      'channel'          => 'myChannelCode',
     *      'configuration' => [
     *          'ecommerce' => ['scale' => ['ratio' => 0.5]],
     *          'tablet'    => ['scale' => ['ratio' => 0.25]],
     *          'mobile'    => [
     *              'scale'      => ['width'      => 200],
     *              'colorspace' => ['colorspace' => 'gray'],
     *          ],
     *          'print'     => ['resize' => ['width' => 400, 'height' => 200]],
     *      ],
     * ]
     *
     * After:
     * [
     *      'code'       => 'myChannelCode',
     *      'configuration' => [
     *          'ecommerce' => ['scale' => ['ratio' => 0.5]],
     *          'tablet'    => ['scale' => ['ratio' => 0.25]],
     *          'mobile'    => [
     *              'scale'      => ['width'      => 200],
     *              'colorspace' => ['colorspace' => 'gray'],
     *          ],
     *          'print'     => ['resize' => ['width' => 400, 'height' => 200]],
     *      ]
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
            case 'channel':
                $convertedItem['code'] = (string) $data;
                break;
            case 'configuration':
                $convertedItem[$field] = $data;
                break;
        }

        return $convertedItem;
    }
}
