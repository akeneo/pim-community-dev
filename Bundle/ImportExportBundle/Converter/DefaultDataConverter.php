<?php

namespace Oro\Bundle\ImportExportBundle\Converter;

use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class DefaultDataConverter implements DataConverterInterface
{
    /**
     * Default delimiter for parts of key
     *
     * @var string
     */
    protected $convertDelimiter = ':';

    /**
     * {@inheritDoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        return $this->convertToPlainData($exportedRecord, $skipNullValues);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        return $this->convertToComplexData($importedRecord, $skipNullValues);
    }

    /**
     * @param array $complexData
     * @param boolean $skipNullValues
     * @return array
     * @throws LogicException
     */
    protected function convertToPlainData(array $complexData, $skipNullValues = true)
    {
        $plainData = array();

        foreach ($complexData as $key => $value) {
            $key = (string)$key;
            if (strpos($key, $this->convertDelimiter) !== false) {
                throw new LogicException(sprintf('Delimiter "%s" is not allowed in keys', $this->convertDelimiter));
            }

            if (is_array($value)) {
                // recursive invocation and setting
                $result = $this->convertToPlainData($value, $skipNullValues);
                foreach ($result as $tmpKey => $tmpValue) {
                    $compositeKey = $key . $this->convertDelimiter . $tmpKey;
                    $plainData[$compositeKey] = $tmpValue;
                }
            } elseif ($value !== null || !$skipNullValues) {
                $plainData[$key] = (string)$value;
            }
        }

        return $plainData;
    }

    /**
     * @param array $plainData
     * @param boolean $skipNullValues
     * @return array
     */
    protected function convertToComplexData(array $plainData, $skipNullValues = true)
    {
        $complexData = array();

        foreach ($plainData as $compositeKey => $value) {
            if ($value !== null || !$skipNullValues) {
                $keys = explode($this->convertDelimiter, $compositeKey);
                $complexData = $this->setValueByKeys($complexData, $keys, $value);
            }
        }

        return $complexData;
    }

    /**
     * @param array $data
     * @param array $keys
     * @param mixed $value
     * @return array
     * @throws LogicException
     */
    protected function setValueByKeys(array $data, array $keys, $value)
    {
        $currentKey = array_shift($keys);
        if (empty($keys)) {
            $data[$currentKey] = $value;
        } else {
            if (!array_key_exists($currentKey, $data)) {
                $data[$currentKey] = array();
            }
            if (!is_array($data[$currentKey])) {
                throw new LogicException(sprintf('Can\'t set nested value under key "%s"', $currentKey));
            }

            $data[$currentKey] = $this->setValueByKeys($data[$currentKey], $keys, $value);
        }

        return $data;
    }
}
