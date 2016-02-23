<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\Buffer\BufferInterface;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

/**
 * Write product data into a XLSX file on the local filesystem
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxProductWriter extends AbstractFileWriter
{
    /** @var BufferInterface */
    protected $buffer;

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

    /** @var string */
    protected $filePath = '/tmp/export_%datetime%.xslx';

    /** @var FileExporterInterface */
    protected $fileExporter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param BufferFactory             $bufferFactory
     * @param FileExporterInterface     $fileExporter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        BufferFactory $bufferFactory,
        FileExporterInterface $fileExporter
    ) {
        parent::__construct($filePathResolver);

        $this->buffer       = $bufferFactory->create();
        $this->fileExporter = $fileExporter;
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
            $product = $item['product'];
            if ($this->isWithHeader()) {
                $this->addToHeaders(array_keys($product));
            }

            $this->buffer->write($product);
        }

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        foreach ($items as $item) {
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $writer = WriterFactory::create(Type::XLSX); // for XLSX files

        $writer->openToFile($this->getPath()); // write data to a file or to a PHP stream

        $headers = $this->isWithHeader() ? $this->headers : [];
        $writer->addRow($headers);

        $hollowItem = array_fill_keys($this->headers, '');
        foreach ($this->buffer as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item); // add a row at a time

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $writer->close();
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!isset($config['mainContext'])) {
            return;
        }

        foreach ($config['mainContext'] as $key => $value) {
            $this->filePathResolverOptions['parameters']['%' . $key . '%'] = $value;
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
     * @param array $media
     */
    protected function copyMedia(array $media)
    {
        $target = dirname($this->getPath()) . DIRECTORY_SEPARATOR . $media['exportPath'];

        if (!is_dir(dirname($target))) {
            $this->localFs->mkdir(dirname($target));
        }

        try {
            $this->fileExporter->export($media['filePath'], $target, $media['storageAlias']);
            $this->writtenFiles[$target] = $media['exportPath'];
        } catch (FileTransferException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been found or is not currently available',
                [],
                $media
            );
        } catch (\LogicException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                sprintf('The media has not been copied. %s', $e->getMessage()),
                [],
                $media
            );
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
