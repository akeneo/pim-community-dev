<?php

namespace Acme\Bundle\XmlConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Buffer\BufferFactory;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;

/**
 * Write data into an xml file on the filesystem
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlWriter extends AbstractFileWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    ArchivableWriterInterface,
    StepExecutionAwareInterface
{
    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var FlatItemBuffer */
    protected $flatRowBuffer = null;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var BufferFactory */
    protected $bufferFactory;

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $writtenFiles = [];

    /** @var \XMLWriter **/
    protected $xml;
    /**
     * @param ArrayConverterInterface   $arrayConverter
     * @param BufferFactory             $bufferFactory
     * @param FlatItemBufferFlusher     $flusher
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher
    ) {
        parent::__construct();

        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null === $this->xml) {
            $filePath = $this->stepExecution->getJobParameters()->get('filePath');

            $this->xml = new \XMLWriter();
            $this->xml->openURI($filePath);
            $this->xml->startDocument('1.0', 'UTF-8');
            $this->xml->setIndent(4);
            $this->xml->startElement('products');
        }
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
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        foreach ($items as $item) {
            $flatItem = $this->arrayConverter->convert($item);

            $this->xml->startElement('product');
            foreach ($flatItem as $property => $value) {
                $this->xml->writeAttribute($property, $value);
            }
            $this->xml->endElement();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->xml->endElement();
        $this->xml->endDocument();
        $this->xml->flush();

        $this->writtenFiles = [$this->stepExecution->getJobParameters()->get('filePath')];
        $this->flusher->setStepExecution($this->stepExecution);
    }
}
