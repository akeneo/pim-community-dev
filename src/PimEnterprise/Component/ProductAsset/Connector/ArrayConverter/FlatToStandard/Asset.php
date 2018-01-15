<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Exception\DataArrayConversionException;

/**
 * Product Asset Flat Converter
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Asset implements ArrayConverterInterface
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
     *      'categories'    => 'myCat1,myCat2,myCat3'
     *      'tags'          => 'dog,flowers,cities,animal,sunset',
     *      'end_of_use'    => '2018-02-01',
     * ]
     *
     * After:
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
     *      'end_of_use'  => '2018-02-01',
     * ]
     *
     * TODO: change naming "localized" in "localizable" in major version 3.0 (BC break)
     *
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItem = ['tags' => [], 'categories' => []];
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
    protected function convertField(array $convertedItem, $field, $data)
    {
        switch ($field) {
            case 'code':
            case 'description':
                $convertedItem[$field] = (string) $data;
                break;
            case 'end_of_use':
                $convertedItem[$field] = $this->convertDate($data);
                break;
            case 'localized':
                $convertedItem['localizable'] = (bool) $data;
                break;
            case 'tags':
                $convertedItem['tags'] = array_unique(explode(',', $data));
                break;
            case 'categories':
                $convertedItem['categories'] = array_unique(explode(',', $data));
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
     * @throws DataArrayConversionException
     */
    protected function validateRequiredFields(array $item, array $requiredFields)
    {
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, array_keys($item))) {
                throw new DataArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }

            if ('' === $item[$requiredField]) {
                throw new DataArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }

        if (!in_array($item['localized'], ['0', '1'])) {
            throw new DataArrayConversionException(
                'Localized field contains invalid data only "0" or "1" is accepted'
            );
        }
    }

    /**
     * Return the value if it's not a date (launch an exception should not be done here).
     * "2015-12-31" will be converted to "2015-12-31T00:00:00+01:00"
     *
     * These dates are wrong and will not converted:
     * "2015/12/31"
     * "2015-45-31"
     * "not a date"
     *
     * @param mixed $date
     *
     * @return string|null
     */
    protected function convertDate($date)
    {
        if ('' === $date || null === $date) {
            return null;
        }

        $datetime = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();

        if (0 === $errors['warning_count'] && 0 === $errors['error_count']) {
            $datetime->setTime(0, 0, 0);

            return $datetime->format('c');
        }

        return $date;
    }
}
