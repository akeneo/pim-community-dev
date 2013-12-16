<?php

namespace Pim\Bundle\ImportExportBundle\Writer\File;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Product file writer
 *
 * This writer is specialized in writing product file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends FileWriter implements ArchivableWriterInterface
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var array */
    protected $writtenFiles = array();

    /**
     * Constructor
     *
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
        parent::write(
            array_map(
                function ($item) {
                    return $item['entry'];
                },
                $items
            )
        );

        $this->writtenFiles[$this->getPath()] = basename($this->getPath());

        foreach ($items as $data) {
            foreach ($data['media'] as $media) {
                if ($media) {
                    $this->copyMedia($media);
                }
            }
        }
    }

    /**
     * @param Media $media
     *
     * @return null
     */
    protected function copyMedia(Media $media)
    {
        $result = $this->mediaManager->copy($media, dirname($this->getPath()));
        if ($result === true) {
            $exportPath = $this->mediaManager->getExportPath($media);
            $this->writtenFiles[sprintf('%s/%s', dirname($this->getPath()), $exportPath)] = $exportPath;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }
}
