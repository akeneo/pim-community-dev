<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /** @var RawFileFetcherInterface */
    protected $fileFetcher;

    /** @var MountManager */
    protected $mountManager;

    /** @var Filesystem */
    protected $localFs;

    /**
     * @param MountManager            $mountManager
     * @param RawFileFetcherInterface $fileFetcher
     */
    public function __construct(MountManager $mountManager, RawFileFetcherInterface $fileFetcher)
    {
        $this->mountManager = $mountManager;
        $this->fileFetcher = $fileFetcher;
        $this->localFs = new Filesystem();
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
                    $this->copyMedia($media, $exportDirectory);
                }
            }
        }

        $this->items = array_merge($this->items, $products);
    }

    /**
     * @param array  $media
     * @param string $exportDirectory
     */
    protected function copyMedia(array $media, $exportDirectory)
    {
        $rawFile = $this->fetchMediaFile($media);

        if (null !== $rawFile) {
            $exportPathname = $exportDirectory . DIRECTORY_SEPARATOR . $media['exportPath'];
            if ($this->copyFile($rawFile->getPathname(), $exportPathname)) {
                $this->writtenFiles[$exportPathname] = $media['exportPath'];
            } else {
                $this->stepExecution->addWarning(
                    $this->getName(),
                    'The media has not been copied',
                    [],
                    $media
                );
            }
            //TODO: files should be available in the archive folder to be able to generate the ZIP file
//            $this->moveFile($rawFile->getPathname(), 'ARCHIVE/' . $media['exportPath']);
        }
    }

    /**
     * @param array $media
     *
     * @return null|\SplFileInfo
     */
    protected function fetchMediaFile(array $media)
    {
        $rawFile = null;
        $storageFs = $this->mountManager->getFilesystem($media['storageAlias']);

        try {
            $rawFile = $this->fileFetcher->fetch($media['filePath'], $storageFs);
        } catch (\LogicException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been found on the file storage',
                [],
                $media
            );
        } catch (FileTransferException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                'Impossible to copy the media from the file storage',
                [],
                $media
            );
        }

        return $rawFile;
    }


    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    protected function copyFile($source, $destination)
    {
        $destinationDir = dirname($destination);

        try {
            if (!is_dir($destinationDir)) {
                $this->localFs->mkdir($destinationDir);
            }
            $this->localFs->copy($source, $destination);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    protected function moveFile($source, $destination)
    {
        $destinationDir = dirname($destination);

        try {
            if (!is_dir($destinationDir)) {
                $this->localFs->mkdir($destinationDir);
            }
            $this->localFs->rename($source, $destination);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }
}
