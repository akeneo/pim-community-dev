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
    public function convertToExportFormat(array $importedRecord)
    {
        return $this->convertToPlainData($importedRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $exportedRecord)
    {
        return $this->convertToComplexData($exportedRecord);
    }

    /**
     * @param array $complexData
     * @return array
     * @throws LogicException
     */
    protected function convertToPlainData(array $complexData)
    {
        $plainData = array();

        foreach ($complexData as $key => $value) {
            $key = (string)$key;
            if (strpos($key, $this->convertDelimiter) !== false) {
                throw new LogicException(sprintf('Delimiter "%s" is not allowed in keys', $this->convertDelimiter));
            }

            if (is_array($value)) {
                // recursive invocation and setting
                $result = $this->convertToPlainData($value);
                foreach ($result as $tmpKey => $tmpValue) {
                    $compositeKey = $key . $this->convertDelimiter . $tmpKey;
                    $plainData[$compositeKey] = $tmpValue;
                }
            } else {
                $plainData[$key] = (string)$value;
            }
        }

        return $plainData;
    }

    /**
     * @param array $plainData
     * @return array
     */
    protected function convertToComplexData(array $plainData)
    {
        $complexData = array();

        foreach ($plainData as $compositeKey => $value) {
            $keys = explode($this->convertDelimiter, $compositeKey);
            $complexData = $this->setValueByKeys($complexData, $keys, $value);
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
