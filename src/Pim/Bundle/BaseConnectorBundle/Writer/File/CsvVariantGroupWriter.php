<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Pim\Component\Connector\Writer\File\FileExporterInterface;

/**
 * Write variant group data into a csv file on the filesystem
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvVariantGroupWriter extends CsvWriter
{
    /** @var FileExporterInterface */
    protected $fileExporter;

    /**
     * @param FileExporterInterface $fileExporter
     */
    public function __construct(FileExporterInterface $fileExporter)
    {
        $this->fileExporter = $fileExporter;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $variantGroups = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }

        $this->items = array_merge($this->items, $variantGroups);
    }

    /**
     * @param array $media
     */
    protected function copyMedia(array $media)
    {
        $target = dirname($this->getPath()) . DIRECTORY_SEPARATOR . $media['exportPath'];

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
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
