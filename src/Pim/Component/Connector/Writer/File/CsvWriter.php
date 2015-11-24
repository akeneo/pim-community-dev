<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Write data into a csv file on the filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter extends AbstractFileWriter implements ArchivableWriterInterface
{
    /** @var BufferInterface */
    protected $buffer;

    /** @var string */
    protected $filePath = '/tmp/export_%datetime%.csv';

    /** @var string */
    protected $delimiter = ';';

    /** @var string */
    protected $enclosure = '"';

    /** @var bool */
    protected $withHeader = true;

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $writtenFiles = [];

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param BufferFactory             $bufferFactory
     */
    public function __construct(FilePathResolverInterface $filePathResolver, BufferFactory $bufferFactory)
    {
        parent::__construct($filePathResolver);

        $this->buffer = $bufferFactory->create();
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
        $this->withHeader = (bool) $withHeader;
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
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            if ($this->isWithHeader()) {
                $this->addToHeaders(array_keys($item));
            }

            $this->buffer->write($item);
        }
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $csvFile = $this->createCsvFile();

        $headers = $this->isWithHeader() ? $this->headers : [];
        $this->writeToCsvFile($csvFile, $headers);

        $hollowItem = array_fill_keys($this->headers, '');
        foreach ($this->buffer as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $this->writeToCsvFile($csvFile, $item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        fclose($csvFile);
        $this->writtenFiles[$this->getPath()] = basename($this->getPath());
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
                            'label' => 'pim_connector.export.delimiter.label',
                            'help'  => 'pim_connector.export.delimiter.help'
                        ]
                    ],
                    'enclosure' => [
                        'options' => [
                            'label' => 'pim_connector.export.enclosure.label',
                            'help'  => 'pim_connector.export.enclosure.help'
                        ]
                    ],
                    'withHeader' => [
                        'type'    => 'switch',
                        'options' => [
                            'label' => 'pim_connector.export.withHeader.label',
                            'help'  => 'pim_connector.export.withHeader.help'
                        ]
                    ],
                ]
            );
    }

    /**
     * Create the file to write to and return its pointer
     *
     * @throws RuntimeErrorException
     *
     * @return resource
     */
    protected function createCsvFile()
    {
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        if (false === $file = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPath()]);
        }

        return $file;
    }

    /**
     * Write a csv formatted line into the specified file. If an error occurs the file is closed and an exception is
     * thrown.
     *
     * @param resource $csvFile
     * @param array    $data
     *
     * @throws RuntimeErrorException
     */
    protected function writeToCsvFile($csvFile, array $data)
    {
        if (false === fputcsv($csvFile, $data, $this->delimiter, $this->enclosure)) {
            fclose($csvFile);
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }
    }

    /**
     * Add the specified keys to the list of headers
     *
     * @param array $keys
     */
    protected function addToHeaders(array $keys)
    {
        $headers = array_merge($this->headers, $keys);
        $headers = array_unique($headers);

        $identifier = array_shift($headers);
        natsort($headers);
        array_unshift($headers, $identifier);

        $this->headers = $headers;
    }
}
