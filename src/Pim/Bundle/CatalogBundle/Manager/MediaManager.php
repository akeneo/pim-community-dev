<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;
use Pim\Bundle\CatalogBundle\Model\Media;
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
    /** @var Filesystem */
    protected $filesystem;

    /** @var string */
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
     *
     * @throws MediaManagementException
     */
    public function handle(Media $media, $filenamePrefix)
    {
        try {
            if ($file = $media->getFile()) {
                if ($media->getFilename() && $this->fileExists($media)) {
                    $this->delete($media);
                }
                $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();
                $this->upload($media, $this->generateFilename($filename, $filenamePrefix));
            } elseif ($media->isRemoved() && $this->fileExists($media)) {
                $this->delete($media);
            }
        } catch (\Exception $e) {
            throw new MediaManagementException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Duplicate a media information into another one
     *
     * @param Media  $source
     * @param Media  $target
     * @param string $filenamePrefix
     */
    public function duplicate(Media $source, Media $target, $filenamePrefix)
    {
        $target->setFile(new File($source->getFilePath()));
        $this->upload(
            $target,
            $this->generateFilename(
                $source->getOriginalFilename(),
                $filenamePrefix
            )
        );
        $target->setOriginalFilename($source->getOriginalFilename());
    }

    /**
     * @param Media  $media
     * @param string $targetDir
     *
     * @return boolean true on success, false on failure
     */
    public function copy(Media $media, $targetDir)
    {
        if ($media->getFilePath() === null) {
            return false;
        }

        $targetDir = sprintf('%s/%s', $targetDir, $this->getExportPath($media));

        if (!is_dir(dirname($targetDir))) {
            mkdir(dirname($targetDir), 0777, true);
        }

        return copy($media->getFilePath(), $targetDir);
    }

    /**
     * Get the export path of the media
     *
     * Examples:
     *   - files/sku-001/front_view/en_US/ecommerce
     *   - files/sku-002/manual/ecommerce
     *   - files/sku-003/back_view/en_US
     *   - files/sku-004/insurance
     *
     * @param Media $media
     *
     * @return string
     */
    public function getExportPath(Media $media)
    {
        if ($media->getFilePath() === null) {
            return '';
        }

        $value     = $media->getValue();
        $attribute = $value->getAttribute();
        $target    = sprintf(
            'files/%s/%s',
            $value->getEntity()->getIdentifier(),
            $attribute->getCode()
        );

        if ($attribute->isLocalizable()) {
            $target .= '/' . $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $target .= '/' . $value->getScope();
        }

        return $target . '/' . $media->getOriginalFilename();
    }

    /**
     * @param File   $filename
     * @param string $filenamePrefix
     *
     * @return string
     */
    protected function generateFilename($filename, $filenamePrefix)
    {
        return sprintf('%s-%s', $filenamePrefix, $filename);
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

    /**
     * Get the media, base64 encoded
     * @param Media $media
     *
     * @return string
     */
    public function getBase64(Media $media)
    {
        return base64_encode(file_get_contents($this->getFilePath($media)));
    }
}
