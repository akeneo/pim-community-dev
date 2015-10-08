<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Symfony\Component\Filesystem\Filesystem;
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
     *
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     *
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var bool
     */
    protected $withHeader = true;

    /**
     * @var array
     */
    protected $writtenFiles = [];

    /**
     * @var array
     */
    protected $items = [];

    /** @var Filesystem */
    protected $localFs;

    public function __construct()
    {
        $this->localFs = new Filesystem();
    }

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
     * @param bool $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }

    /**
     * Get whether or not to print a header row into the csv
     *
     * @return bool
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
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        $this->writtenFiles[$this->getPath()] = basename($this->getPath());

        $uniqueKeys = $this->getAllKeys($this->items);
        $fullItems  = $this->mergeKeys($uniqueKeys);
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
                [
                    'delimiter' => [
                        'options' => [
                            'label' => 'pim_base_connector.export.delimiter.label',
                            'help'  => 'pim_base_connector.export.delimiter.help'
                        ]
                    ],
                    'enclosure' => [
                        'options' => [
                            'label' => 'pim_base_connector.export.enclosure.label',
                            'help'  => 'pim_base_connector.export.enclosure.help'
                        ]
                    ],
                    'withHeader' => [
                        'type'    => 'switch',
                        'options' => [
                            'label' => 'pim_base_connector.export.withHeader.label',
                            'help'  => 'pim_base_connector.export.withHeader.help'
                        ]
                    ],
                ]
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
        $uniqueKeys = array_unique($mergedKeys);

        $identifier = array_shift($uniqueKeys);
        sort($uniqueKeys, SORT_FLAG_CASE | SORT_NATURAL);
        array_unshift($uniqueKeys, $identifier);

        return $uniqueKeys;
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
            $fullItems[] = array_replace($uniqueKeys, $item);
        }

        return $fullItems;
    }
}
