<?php

namespace Pim\Component\Connector\Writer\File\Csv;

use Pim\Component\Connector\Writer\File\BulkFileExporter;
use Pim\Component\Connector\Writer\File\ColumnSorterInterface;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends Writer
{
    /** @var BulkFileExporter */
    protected $mediaCopier;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $mediaCopier
     * @param ColumnSorterInterface     $columnSorter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $mediaCopier,
        ColumnSorterInterface $columnSorter
    ) {
        parent::__construct($filePathResolver, $flatRowBuffer, $columnSorter);

        $this->mediaCopier = $mediaCopier;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = $media = [];
        foreach ($items as $item) {
            $products[] = $item['product'];
            $media[] = $item['media'];
        }

        parent::write($products);

        $parameters = $this->stepExecution->getJobParameters();

        if ($parameters->has('with_media') && !$parameters->get('with_media')) {
            return;
        }

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        $this->mediaCopier->exportAll($media, $exportDirectory);

        foreach ($this->mediaCopier->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->mediaCopier->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $error['message'],
                [],
                $error['medium']
            );
        }
    }
}
