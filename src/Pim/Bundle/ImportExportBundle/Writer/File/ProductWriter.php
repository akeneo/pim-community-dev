<?php

namespace Pim\Bundle\ImportExportBundle\Writer\File;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Product file writer
 *
 * This writer is specialized in writing product file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends FileWriter
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var \ZipArchive */
    protected $archive;

    /** @var string */
    protected $archivePath;

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
    public function getPath()
    {
        return $this->archivePath ?: parent::getPath();
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

        foreach ($items as $data) {
            foreach ($data['media'] as $media) {
                if ($media) {
                    $result = $this->mediaManager->copy($media, $this->directoryName);
                    if ($result === true) {
                        $exportPath = $this->mediaManager->getExportPath($media);
                        $this->addToArchive(sprintf('%s/%s', $this->directoryName, $exportPath), $exportPath);
                    }
                }
            }
        }

        if (null !== $this->archive) {
            $this->archive->close();
        }
    }

    /**
     * Add a file to the archive.
     * The archive is created only if this method is called at least once.
     *
     * @param string $fullPath
     * @param string $localPath
     *
     * @throws \RuntimeException If an error occurs when creating the archive
     */
    protected function addToArchive($fullPath, $localPath)
    {
        if (null === $this->archive) {
            $baseFile = $this->getPath();
            $this->archive = new \ZipArchive();
            $this->archivePath = sprintf('%s/%s.zip',
                pathinfo($baseFile, PATHINFO_DIRNAME),
                pathinfo($baseFile, PATHINFO_FILENAME)
            );

            $status = $this->archive->open($this->archivePath, \ZIPARCHIVE::CREATE);

            if ($status !== true) {
                throw new \RuntimeException(sprintf('Error "%d" occured when creating the zip archive.', $status));
            }

            $this->addToArchive($baseFile, basename($baseFile));
        }

        $status = $this->archive->addFile($fullPath, $localPath);
        if ($status !== true) {
            throw new \RuntimeException(
                sprintf(
                    'Unknown error occured when adding file "%s" to the zip archive.',
                    $fullPath
                )
            );
        }
    }
}
