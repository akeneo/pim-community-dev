<?php
namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\FlexibleEntityBundle\Entity\Media;

use Knp\Bundle\GaufretteBundle\FilesystemMap;

/**
 * Media Manager actually implements with Gaufrette Bundle and Local adapter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
        $this->fileSystem = $fileSystemMap->get($fileSystemName);
        $this->uploadDirectory  = $uploadDirectory;
    }

    /**
     * Upload file
     * @param Media   $media     Media entity
     * @param string  $filename  Filename
     * @param boolean $overwrite Overwrite file or not
     */
    public function upload(Media $media, $filename, $overwrite = false)
    {
        // prepare upload
        $uploadedFile = $media->getFile();
        $content = file_get_contents($uploadedFile->getPathname());

        // write file
        $this->write($filename, $content, $overwrite);

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
     * @param Media  $media
     *
     * @return content
     */
    public function getFilePath(Media $media)
    {
        if ($this->fileSystem->has($media->getFilename())) {
            return $this->uploadDirectory .'/'. $media->getFilename();
        }
    }

    /**
     * Delete a file
     * @param Media $media
     */
    public function delete(Media $media)
    {
        $this->fileSystem->delete($media->getFilename());
    }

    /**
     * Predicate to know if file exists physically
     *
     * @param Media $media
     *
     * @return boolean
     */
    public function fileExists(Media $media)
    {
        $filePath = $this->uploadDirectory .'/'. $media->getFilename();

        return $media->getFilename() !== null && file_exists($filePath);
    }
}
