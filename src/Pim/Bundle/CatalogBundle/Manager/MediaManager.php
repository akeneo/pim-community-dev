<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;
use Gedmo\Sluggable\Util\Urlizer;
use Pim\Bundle\CatalogBundle\Model\AbstractMedia;
use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

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
     * @param AbstractMedia $media
     * @param string        $filenamePrefix
     *
     * @throws MediaManagementException
     */
    public function handle(AbstractMedia $media, $filenamePrefix)
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
     * @param AbstractMedia $source
     * @param AbstractMedia $target
     * @param string        $filenamePrefix
     */
    public function duplicate(AbstractMedia $source, AbstractMedia $target, $filenamePrefix)
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
     * @param AbstractMedia $media
     * @param string        $targetDir
     *
     * @return boolean true on success, false on failure
     */
    public function copy(AbstractMedia $media, $targetDir)
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
     * @param AbstractMedia $media
     *
     * @return string
     */
    public function getExportPath(AbstractMedia $media)
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
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function generateFilenamePrefix(ProductInterface $product, ProductValueInterface $value)
    {
        return sprintf(
            '%s-%s-%s-%s-%s-%s',
            $product->getId(),
            Urlizer::urlize($product->getIdentifier(), '_'),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
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
     * @param AbstractMedia $media     AbstractMedia entity
     * @param string        $filename  Filename
     * @param boolean       $overwrite Overwrite file or not
     */
    protected function upload(AbstractMedia $media, $filename, $overwrite = false)
    {
        if (($file = $media->getFile())) {
            if ($file instanceof UploadedFile && UPLOAD_ERR_OK !== $file->getError()) {
                return;
            }

            $this->write($filename, file_get_contents($file->getPathname()), $overwrite);

            $media->setOriginalFilename(
                $file instanceof UploadedFile ?  $file->getClientOriginalName() : $file->getFilename()
            );
            $media->setFilename($filename);
            $media->setFilepath($this->getFilePath($media));
            $media->setMimeType($file->getMimeType());
        }
    }

    /**
     * Write file in filesystem
     *
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
     * @param AbstractMedia $media
     *
     * @return content
     */
    protected function getFilePath(AbstractMedia $media)
    {
        if ($this->fileExists($media)) {
            return $this->uploadDirectory . DIRECTORY_SEPARATOR . $media->getFilename();
        }
    }

    /**
     * Delete a file
     * @param AbstractMedia $media
     */
    protected function delete(AbstractMedia $media)
    {
        if (($media->getFilename() !== "") && $this->fileExists($media)) {
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
     * @param AbstractMedia $media
     *
     * @return boolean
     */
    protected function fileExists(AbstractMedia $media)
    {
        return $this->filesystem->has($media->getFilename());
    }

    /**
     * Get the media, base64 encoded
     * @param AbstractMedia $media
     *
     * @return string
     */
    public function getBase64(AbstractMedia $media)
    {
        return base64_encode(file_get_contents($this->getFilePath($media)));
    }
}
