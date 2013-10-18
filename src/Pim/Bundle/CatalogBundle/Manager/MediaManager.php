<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Oro\Bundle\FlexibleEntityBundle\Entity\Media;
use Gaufrette\Filesystem;
use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;

/**
 * Media Manager actually implements with Gaufrette Bundle and Local adapter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaManager
{
    /**
     * File system
     * @var \Gaufrette\Filesystem
     */
    protected $filesystem;

    /**
     * Upload directory
     *
     * @var string
     */
    protected $uploadDirectory;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param string     $uploadDirectory
     */
    public function __construct(Filesystem $filesystem, $uploadDirectory)
    {
        $this->filesystem      = $filesystem;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * @param Media  $media
     * @param string $filenamePrefix
     */
    public function handle(Media $media, $filenamePrefix)
    {
        try {
            if ($file = $media->getFile()) {
                if ($media->getFilename() && $this->fileExists($media)) {
                    $this->delete($media);
                }
                $this->upload($media, $this->generateFilename($file, $filenamePrefix));
            } elseif ($media->isRemoved() && $this->fileExists($media)) {
                $this->delete($media);
            }
        } catch (\Exception $e) {
            throw new MediaManagementException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function copy(Media $media, $targetDir)
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFilePath = sprintf('%s/%s', rtrim($targetDir, '/'), $media->getFilename());
        copy($media->getFilePath(), $targetFilePath);

        return $targetFilePath;
    }

    /**
     * @param File   $file
     * @param string $filenamePrefix
     *
     * @return string
     */
    protected function generateFilename(File $file, $filenamePrefix)
    {
        return sprintf(
            '%s-%s',
            $filenamePrefix,
            $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename()
        );
    }

    /**
     * Upload file
     * @param Media   $media     Media entity
     * @param string  $filename  Filename
     * @param boolean $overwrite Overwrite file or not
     */
    protected function upload(Media $media, $filename, $overwrite = false)
    {
        $file = $media->getFile();
        $this->write($filename, file_get_contents($file->getPathname()), $overwrite);

        $media->setOriginalFilename(
            $file instanceof UploadedFile ?  $file->getClientOriginalName() : $file->getFilename()
        );
        $media->setFilename($filename);
        $media->setFilepath($this->getFilePath($media));
        $media->setMimeType($file->getMimeType());
    }

    /**
     * Write file in filesystem
     * @param string  $filename  Filename
     * @param string  $content   File content
     * @param boolean $overwrite Overwrite file or not
     */
    protected function write($filename, $content, $overwrite = false)
    {
        $this->filesystem->write($filename, $content, $overwrite);
    }

    /**
     * Read a file
     * @param Media $media
     *
     * @return content
     */
    protected function getFilePath(Media $media)
    {
        if ($this->fileExists($media)) {
            return $this->uploadDirectory . DIRECTORY_SEPARATOR . $media->getFilename();
        }
    }

    /**
     * Delete a file
     * @param Media $media
     */
    protected function delete(Media $media)
    {
        if (($media->getFilename() != "") && $this->fileExists($media)) {
            $this->filesystem->delete($media->getFilename());
        }

        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setFilepath(null);
        $media->setMimeType(null);
    }

    /**
     * Predicate to know if file exists physically
     *
     * @param Media $media
     *
     * @return boolean
     */
    protected function fileExists(Media $media)
    {
        return $this->filesystem->has($media->getFilename());
    }
}
