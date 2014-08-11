<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;

/**
 * Write product data into a csv file on the filesystem
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /**
     * @param MediaManager $mediaManager
     */
    protected $mediaManager;

    /**
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

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
     * @param AbstractProductMedia $media
     *
     * @return void
     */
    protected function copyMedia(AbstractProductMedia $media)
    {
        if (null === $media->getFilePath() || '' === $media->getFileName()) {
            return;
        }
        $result = $this->mediaManager->copy($media, dirname($this->getPath()));
        $exportPath = $this->mediaManager->getExportPath($media);
        if (true === $result) {
            $this->writtenFiles[sprintf('%s/%s', dirname($this->getPath()), $exportPath)] = $exportPath;
        } else {
            $this->stepExecution->addWarning(
                $this->getName(),
                sprintf('Copy of "%s" failed.', $media->getFilename()),
                [],
                $media
            );
        }
    }
}
