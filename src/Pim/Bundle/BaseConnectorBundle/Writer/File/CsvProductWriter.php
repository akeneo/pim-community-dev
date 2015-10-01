<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Pim\Bundle\ConnectorBundle\Writer\File\ContextableCsvWriter;
use Pim\Component\Connector\Writer\File\FileExporterInterface;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends ContextableCsvWriter
{
    /** @var FileExporterInterface */
    protected $fileExporter;

    /**
     * @param FileExporterInterface $fileExporter
     */
    public function __construct(FileExporterInterface $fileExporter)
    {
        parent::__construct();

        $this->fileExporter = $fileExporter;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = [];

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        foreach ($items as $item) {
            $products[] = $item['product'];
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }

        $this->items = array_merge($this->items, $products);
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
}
