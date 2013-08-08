<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Entity\Media;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Media Manager actually implements with Gaufrette Bundle and Local adapter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MediaManager
{
    /**
     * File system
     * @var \Gaufrette\Filesystem
     */
    protected $fileSystem;

    /**
     * Upload directory
     *
     * @var string
     */
    protected $uploadDirectory;

    /**
     * Constructor
     * @param FilesystemMap $fileSystemMap   Filesystem map
     * @param string        $fileSystemName  Filesystem name
     * @param string        $uploadDirectory Upload directory
     */
    public function __construct(FilesystemMap $fileSystemMap, $fileSystemName, $uploadDirectory)
    {
        $this->fileSystem      = $fileSystemMap->get($fileSystemName);
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * @param Media  $media
     * @param string $filenamePrefix
     */
    public function handle(Media $media, $filenamePrefix)
    {
        if ($file = $media->getFile()) {
            if ($media->getFilename() && $this->fileExists($media)) {
                $this->delete($media);
            }
            $this->upload($media, $this->generateFilename($file, $filenamePrefix));
        } elseif ($media->isRemoved() && $this->fileExists($media)) {
            $this->delete($media);
        }
    }

    /**
     * @param File   $file
     * @param string $filenamePrefix
     *
     * @return string
     */
    protected function generateFilename(File $file, $filenamePrefix)
    {
        return sprintf('%s-%s', $filenamePrefix, $file->getClientOriginalName());
    }

    /**
     * Upload file
     * @param Media   $media     Media entity
     * @param string  $filename  Filename
     * @param boolean $overwrite Overwrite file or not
     */
    protected function upload(Media $media, $filename, $overwrite = false)
    {
        $uploadedFile = $media->getFile();
        $this->write($filename, file_get_contents($uploadedFile->getPathname()), $overwrite);

        $media->setOriginalFilename($uploadedFile->getClientOriginalName());
        $media->setFilename($filename);
        $media->setFilepath($this->getFilePath($media));
        $media->setMimeType($uploadedFile->getMimeType());
    }

    /**
     * Write file in filesystem
     * @param string  $filename  Filename
     * @param string  $content   File content
     * @param boolean $overwrite Overwrite file or not
     */
    protected function write($filename, $content, $overwrite = false)
    {
        $this->fileSystem->write($filename, $content, $overwrite);
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
        $this->fileSystem->delete($media->getFilename());

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
        return $this->fileSystem->has($media->getFilename());
    }
}
