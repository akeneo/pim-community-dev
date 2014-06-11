<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Write data into a csv file on the filesystem
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter extends FileWriter
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var boolean
     */
    protected $withHeader = true;

    /**
     * Set the csv delimiter character
     *
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     *
     * @var array
     */
    protected $items = [];

    /**
     * Get the csv delimiter character
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set the csv enclosure character
     *
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    /**
     * Get the csv enclosure character
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set whether or not to print a header row into the csv
     *
     * @param boolean $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }

    /**
     * Get whether or not to print a header row into the csv
     *
     * @return boolean
     */
    public function isWithHeader()
    {
        return $this->withHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return
            array_merge(
                parent::getConfigurationFields(),
                array(
                    'delimiter' => array(
                        'options' => array(
                            'label' => 'pim_base_connector.export.delimiter.label',
                            'help'  => 'pim_base_connector.export.delimiter.help'
                        )
                    ),
                    'enclosure' => array(
                        'options' => array(
                            'label' => 'pim_base_connector.export.enclosure.label',
                            'help'  => 'pim_base_connector.export.enclosure.help'
                        )
                    ),
                    'withHeader' => array(
                        'type' => 'switch',
                        'options' => array(
                            'label' => 'pim_base_connector.export.withHeader.label',
                            'help'  => 'pim_base_connector.export.withHeader.help'
                        )
                    ),
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->items = array_merge($this->items, $items);
    }

    /**
     * Get a set of all keys inside arrays
     *
     * @param array $items
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $keys = [];
        foreach ($items as $item) {
            $keys = array_merge($keys, array_keys($item));
        }

        return array_unique($keys);
    }

    /**
     * Merge the keys in arrays
     *
     * @param $uniqueKeys
     *
     * @return array
     */
    protected function mergeKeys($uniqueKeys)
    {
        $uniqueKeys = array_fill_keys($uniqueKeys, '');
        $fullItems = [];
        foreach ($this->items as $item) {
            $fullItems[] = array_merge($uniqueKeys, $item);
        }

        return $fullItems;
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $uniqueKeys = $this->getAllKeys($this->items);
        $fullItems = $this->mergeKeys($uniqueKeys);
        $csvFile = fopen($this->getPath(), 'w');
        if (true == $this->isWithHeader()) {
            fputcsv($csvFile, $uniqueKeys, $this->delimiter);
        } else {
            fputcsv($csvFile, [], $this->delimiter);
        }

        foreach ($fullItems as $item) {
            fputcsv($csvFile, $item, $this->delimiter);
            $this->stepExecution->incrementSummaryInfo('write');
        }

        fclose($csvFile);
    }
}
