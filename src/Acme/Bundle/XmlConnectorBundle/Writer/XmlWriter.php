<?php

namespace Acme\Bundle\XmlConnectorBundle\Writer;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

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
    /** @var array */
    protected $writtenFiles = [];

    /** @var \XMLWriter **/
    protected $xml;

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

            $this->xml->startElement('product');
            foreach ($item as $property => $value) {
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
    }
}
