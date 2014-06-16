<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractMedia;

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
     * @param MediaManager $mediaManager
     */
    protected $mediaManager;

    /**
     * @var array
     */
    protected $writtenFiles = array();

    /**
     * @var array
     */
    protected $items = [];

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
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
        $csvFile = fopen($this->getPath(), 'w');

        if (true == $this->isWithHeader()) {
            fputcsv($csvFile, $uniqueKeys, $this->delimiter);
        } else {
            fputcsv($csvFile, [], $this->delimiter);
        }

        foreach ($fullItems as $item) {
            fputcsv($csvFile, $item, $this->delimiter, $this->enclosure);
            $this->stepExecution->incrementSummaryInfo('write');
        }

        fclose($csvFile);
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
        $products = [];
        foreach ($items as $item) {
            $products[] = $item['product'];
            foreach ($item['media'] as $media) {
                if ($media) {
                    $this->copyMedia($media);
                }
            }
        }

        $this->items = array_merge($this->items, $products);
    }

    /**
     * @param AbstractMedia $media
     *
     * @return null
     */
    protected function copyMedia(AbstractMedia $media)
    {
        $result = $this->mediaManager->copy($media, dirname($this->getPath()));
        $exportPath = $this->mediaManager->getExportPath($media);
        if (true === $result) {
            $this->writtenFiles[sprintf('%s/%s', dirname($this->getPath()), $exportPath)] = $exportPath;
        }
    }

    /**
     * Get a set of all keys inside arrays
     *
     * @param  array $items
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
}
