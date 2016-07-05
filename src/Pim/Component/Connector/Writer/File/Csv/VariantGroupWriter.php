<?php

namespace Pim\Component\Connector\Writer\File\Csv;

use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;

/**
 * CSV variant group writer
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter extends Writer
{
    /** @var BulkFileExporter */
    protected $fileExporter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $fileExporter
     * @param FlatItemBufferFlusher     $flusher
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $fileExporter,
        FlatItemBufferFlusher $flusher
    ) {
        parent::__construct($filePathResolver, $flatRowBuffer, $flusher);

        $this->fileExporter = $fileExporter;
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

        $variantGroups = $media = [];
        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            $media[]         = $item['media'];
        }

        parent::write($variantGroups);
        $this->fileExporter->exportAll($media, $exportDirectory);

        foreach ($this->fileExporter->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->fileExporter->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $error['message'],
                [],
                $error['medium']
            );
        }
    }
}
