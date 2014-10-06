<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Write data into a csv file on the filesystem
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter extends FileWriter implements ArchivableWriterInterface
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
     * @var array
     */
    protected $writtenFiles = array();

    /**
     * @var array
     */
    protected $items = [];

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
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $this->writtenFiles[$this->getPath()] = basename($this->getPath());

        $uniqueKeys = $this->getAllKeys($this->items);
        $fullItems = $this->mergeKeys($uniqueKeys);
        if (false === $csvFile = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPath()]);
        }

        $header = $this->isWithHeader() ? $uniqueKeys : [];
        if (false === fputcsv($csvFile, $header, $this->delimiter)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        foreach ($fullItems as $item) {
            if (false === fputcsv($csvFile, $item, $this->delimiter, $this->enclosure)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } elseif ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
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
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $intKeys = [];
        foreach ($items as $item) {
            $intKeys[] = array_keys($item);
        }

        if (0 === count($intKeys)) {
            return [];
        }

        $mergedKeys = call_user_func_array('array_merge', $intKeys);

        return array_unique($mergedKeys);
    }

    /**
     * Merge the keys in arrays
     *
     * @param array $uniqueKeys
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
}
