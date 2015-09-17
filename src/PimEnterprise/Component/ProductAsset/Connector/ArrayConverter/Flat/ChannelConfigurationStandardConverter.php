<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Channel Variations Configuration Flat Converter
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChannelConfigurationStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'channel'       => 'mycode',
     *      'configuration' => [],
     * ]
     *
     * After:
     * [
     *      'channel'       => 'myChannelCode',
     *      'configuration' => [
     *          'ecommerce' => ['scale' => ['ratio' => 0.5]],
     *          'tablet'    => ['scale' => ['ratio' => 0.25]],
     *          'mobile'    => [
     *              'scale'      => ['width'      => 200],
     *              'colorspace' => ['colorspace' => 'gray'],
     *          ],
     *          'print'     => ['resize' => ['width' => 400, 'height' => 200]],
     *      ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validateRequiredFields($item, ['channel', 'configuration']);
        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, $field, $data)
    {
        switch ($field) {
            case 'channel':
                $convertedItem['channel'] = (string) $data;
                break;
            case 'configuration':
                $convertedItem['configuration'] = $data;
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     * @param array $requiredFields
     *
     * @throws ArrayConversionException
     */
    protected function validateRequiredFields(array $item, array $requiredFields)
    {
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, array_keys($item))) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }

            if ('' === $item[$requiredField]) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }
    }
}
