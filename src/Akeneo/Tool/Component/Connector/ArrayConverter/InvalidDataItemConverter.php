<?php

namespace Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;

/**
 * Invalid data item converter.
 * It flattens an array to a key => string value array.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidDataItemConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $property => $data) {
            $convertedItem[$property] = $this->convertToString($property, $data);
        }

        return $convertedItem;
    }

    /**
     * @throws DataArrayConversionException
     */
    private function convertToString(string $property, $data): string
    {
        $convertedData = null;

        if ($data instanceof \DateTime) {
            $convertedData = $data->format('Y-m-d');
        } elseif (is_array($data)) {
            if (count($data) !== count($data, COUNT_RECURSIVE)) {
                throw new DataArrayConversionException(
                    sprintf('The property "%s" could not be converted into string.', $property)
                );
            }
            $convertedData = implode(',', $data);
        } elseif (is_object($data) && !method_exists($data, '__toString')) {
            throw new DataArrayConversionException(
                    sprintf('The property "%s" could not be converted into string.', $property)
                );
        } else {
            $convertedData = (string) $data;
        }

        if (!is_string($convertedData)) {
            throw new DataArrayConversionException(
                sprintf('The property "%s" could not be converted into string.', $property)
            );
        }

        return $convertedData;
    }
}
