<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Tag Flat Converter
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code'          => 'mycode',
     *      'localized'     => 0,
     *      'description'   => 'My awesome description',
     *      'qualification' => 'dog,flowers,cities,animal,sunset',
     *      'end_of_use_at' => '2018-02-01',
     * ]
     *
     * After:
     * [
     *      'code'        => 'mycode',
     *      'localized'   => false,
     *      'description' => 'My awesome description',
     *      'tags'        => [
     *          'dog',
     *          'flowers',
     *          'cities',
     *          'animal',
     *          'sunset'
     *      ],
     *      'end_of_use_at' => '2018-02-01'
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItem = ['tags' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
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
    protected function convertField($convertedItem, $field, $data)
    {
        switch ($field) {
            case 'code':
            case 'description':
            case 'end_of_use_at':
                $convertedItem[$field] = (string) $data;
                break;
            case 'localized':
                $convertedItem[$field] = (bool) $data;
                break;
            case 'qualification':
                $convertedItem['tags'] = explode(',', $data);
                break;
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->validateRequiredFields($item, ['code', 'localized']);
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

        if (!in_array($item['localized'], ['0', '1'])) {
            throw new ArrayConversionException(
                'Localized field contains invalid data only "0" or "1" is accepted'
            );
        }
    }
}
