<?php

namespace Oro\Bundle\ImportExportBundle\Converter;

use Oro\Bundle\ImportExportBundle\Exception\LogicException;

abstract class AbstractTableDataConverter extends DefaultDataConverter
{
    const BACKEND_TO_FRONTEND = 'backend_to_frontend';
    const FRONTEND_TO_BACKEND = 'frontend_to_backend';

    /**
     * @var array
     */
    protected $backendHeader;

    /**
     * @var array
     */
    protected $backendToFrontendHeader;

    /**
     * {@inheritDoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $plainDataWithBackendHeader = parent::convertToExportFormat($exportedRecord, $skipNullValues);
        $filledPlainDataWithBackendHeader = $this->fillEmptyColumns(
            $this->receiveBackendHeader(),
            $plainDataWithBackendHeader
        );
        $filledPlainDataWithFrontendHints = $this->replaceKeys(
            $this->receiveBackendToFrontendHeader(),
            $filledPlainDataWithBackendHeader
        );

        return $filledPlainDataWithFrontendHints;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $plainDataWithFrontendHeader = $this->removeEmptyColumns($importedRecord, $skipNullValues);
        $frontendHeader = array_keys($plainDataWithFrontendHeader);
        $frontendToBackendHeader = $this->convertHeaderToBackend($frontendHeader);
        $plainDataWithBackendHeader = $this->replaceKeys(
            $frontendToBackendHeader,
            $plainDataWithFrontendHeader
        );
        $complexDataWithBackendHeader = parent::convertToImportFormat($plainDataWithBackendHeader, $skipNullValues);

        return $complexDataWithBackendHeader;
    }

    /**
     * @param array $header
     * @param array $data
     * @return array
     * @throws LogicException
     */
    protected function fillEmptyColumns(array $header, array $data)
    {
        $dataDiff = array_diff(array_keys($data), $header);
        // if data contains keys that are not in header
        if ($dataDiff) {
            throw new LogicException(
                sprintf('Backend header doesn\'t contain fields: %s', implode(', ', $dataDiff))
            );
        }

        $result = array();
        foreach ($header as $headerKey) {
            $result[$headerKey] = array_key_exists($headerKey, $data) ? $data[$headerKey] : '';
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function removeEmptyColumns(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function receiveBackendHeader()
    {
        if (null === $this->backendHeader) {
            $this->backendHeader = $this->getBackendHeader();
        }

        return $this->backendHeader;
    }

    /**
     * @return array
     */
    protected function receiveBackendToFrontendHeader()
    {
        if (null === $this->backendToFrontendHeader) {
            $header = $this->receiveBackendHeader();
            $this->backendToFrontendHeader = $this->convertHeaderToFrontend($header);
        }

        return $this->backendToFrontendHeader;
    }

    /**
     * @param array $backendHeader
     * @return array
     */
    protected function convertHeaderToFrontend(array $backendHeader)
    {
        return $this->convertHeader($backendHeader, self::BACKEND_TO_FRONTEND);
    }

    /**
     * @param array $frontendHeader
     * @return array
     */
    protected function convertHeaderToBackend(array $frontendHeader)
    {
        return $this->convertHeader($frontendHeader, self::FRONTEND_TO_BACKEND);
    }

    /**
     * @param array $header
     * @param string $direction
     * @return array
     */
    protected function convertHeader(array $header, $direction)
    {
        $conversionRules = $this->getHeaderConversionRules();
        $result = array();

        foreach ($header as $hint) {
            $convertedHint = $hint;
            foreach ($conversionRules as $frontendHint => $backendHint) {
                // if regexp should be used
                if (is_array($backendHint)) {
                    if (!empty($backendHint[$direction])) {
                        $convertedHint = $this->applyRegexpConvert($backendHint[$direction], $hint);
                        // only one regexp should be applied
                        if ($convertedHint != $hint) {
                            break;
                        }
                    }
                } elseif ($direction == self::BACKEND_TO_FRONTEND && $hint == $backendHint) {
                    $convertedHint = $frontendHint;
                    break;
                } elseif ($direction == self::FRONTEND_TO_BACKEND && $hint == $frontendHint) {
                    $convertedHint = $backendHint;
                    break;
                }
            }

            $result[$hint] = $convertedHint;
        }

        return $result;
    }

    /**
     * @param array $parameters
     * @param string $value
     * @return string
     */
    protected function applyRegexpConvert(array $parameters, $value)
    {
        if (!empty($parameters[0]) && !empty($parameters[1])) {
            if (is_array($parameters[1]) && is_callable($parameters[1], true) || $parameters[1] instanceof \Closure) {
                $value = preg_replace_callback('~^' . $parameters[0] . '$~', $parameters[1], $value);
            } else {
                $value = preg_replace('~^' . $parameters[0] . '$~', $parameters[1], $value);
            }
        }

        return $value;
    }

    /**
     * Replace keys in data array according to array of replacements
     *
     * @param array $replacementKeys
     * @param array $data
     * @return array
     */
    protected function replaceKeys(array $replacementKeys, array $data)
    {
        $resultData = array();

        foreach ($data as $key => $value) {
            $resultKey = !empty($replacementKeys[$key]) ? $replacementKeys[$key] : $key;
            $resultData[$resultKey] = $value;
        }

        return $resultData;
    }

    /**
     * Get list of rules that should be user to convert,
     *
     * Example: array(
     *     'User Name' => 'userName', // key is frontend hint, value is backend hint
     *     'User Group' => array(     // convert data using regular expression
     *         self::FRONTEND_TO_BACKEND => array('User Group (\d+)', 'userGroup:$1'),
     *         self::BACKEND_TO_FRONTEND => array('userGroup:(\d+)', 'User Group $1'),
     *     )
     * )
     *
     * @return array
     */
    abstract protected function getHeaderConversionRules();

    /**
     * Get maximum backend header for current entity
     *
     * @return array
     */
    abstract protected function getBackendHeader();
}
