<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Gaufrette\Filesystem;
use Gedmo\Sluggable\Util\Urlizer;
use Pim\Bundle\CatalogBundle\Exception\MediaManagementException;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Media Manager uses Gaufrette to manage medias
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

    /** @var ManagerRegistry */
    protected $registry;

    /** @var MediaFactory */
    protected $factory;

    /**
     * Constructor
     *
     * @param Filesystem      $filesystem
     * @param string          $uploadDirectory
     * @param MediaFactory    $factory
     * @param ManagerRegistry $registry
     */
    public function __construct(
        Filesystem $filesystem,
        $uploadDirectory,
        MediaFactory $factory,
        ManagerRegistry $registry
    ) {
        $this->filesystem      = $filesystem;
        $this->uploadDirectory = $uploadDirectory;
        $this->factory         = $factory;
        $this->registry        = $registry;
    }

    /**
     * Handle the medias of a product
     *
     * @param ProductInterface $product
     */
    public function handleProductMedias(ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            if ($media = $value->getMedia()) {
                if ($id = $media->getCopyFrom()) {
                    $repository = $this->doctrine->getRepository('Pim\Bundle\CatalogBundle\Model\ProductMedia');
                    $source = $repository->find($id);
                    if (!$source) {
                        throw new \Exception(
                            sprintf('Could not find media with id %d', $id)
                        );
                    }

                    $this->duplicate(
                        $source,
                        $media,
                        $this->generateFilenamePrefix($product, $value)
                    );
                } else {
                    $filenamePrefix =  $media->getFile() ?
                        $this->generateFilenamePrefix($product, $value) : null;
                    $this->handle($media, $filenamePrefix);
                }
            }
        }
    }

    /**
     * Handles the medias of all products
     *
     * @param ProductInterface[] $products
     */
    public function handleAllProductsMedias(array $products)
    {
        foreach ($products as $product) {
            if (!$product instanceof ProductInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected instance of Pim\Bundle\CatalogBundle\Model\ProductInterface, got %s',
                        get_class($product)
                    )
                );
            }
            $this->handleProductMedias($product);
        }
    }

    /**
     * @param ProductMediaInterface $media
     * @param string                $filenamePrefix
     *
     * @throws MediaManagementException
     */
    public function handle(ProductMediaInterface $media, $filenamePrefix)
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
     * @param ProductMediaInterface $source
     * @param ProductMediaInterface $target
     * @param string                $filenamePrefix
     */
    public function duplicate(ProductMediaInterface $source, ProductMediaInterface $target, $filenamePrefix)
    {
        $target->setFile(new File($source->getFilePath()));
        $filename = $this->generateFilename($source->getOriginalFilename(), $filenamePrefix);
        $this->upload($target, $filename);
        $target->setOriginalFilename($source->getOriginalFilename());
    }

    /**
     * @param ProductMediaInterface $media
     * @param string                $targetDir
     *
     * @return boolean true on success, false on failure
     */
    public function copy(ProductMediaInterface $media, $targetDir)
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
     * Create a media and load file information
     *
     * @param string $filename
     *
     * @return ProductMediaInterface
     *
     * @throws \InvalidArgumentException When file does not exist
     */
    public function createFromFilename($filename)
    {
        $filePath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $filename;
        if (!$this->filesystem->has($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $filePath));
        }
        $media = $this->factory->createMedia();
        $media->setOriginalFilename($filename);
        $media->setFilename($filename);
        $media->setFilePath($filePath);
        $media->setMimeType($this->filesystem->mimeType($filename));

        return $media;
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
     * @param ProductMediaInterface $media
     *
     * @return string
     */
    public function getExportPath(ProductMediaInterface $media)
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
            uniqid(),
            Urlizer::urlize($product->getIdentifier(), '_'),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
    }

    /**
     * Get the media, base64 encoded
     *
     * @param ProductMediaInterface $media
     *
     * @return string|null the base 64 representation of the file media or null if the media has no file attached
     *
     * @throws FileNotFoundException in case the file of the media does not exist or is not readable
     */
    public function getBase64(ProductMediaInterface $media)
    {
        $path = $this->getFilePath($media);

        return $path !== null ? base64_encode(file_get_contents($this->getFilePath($media))) : null;
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
     * Upload a file
     *
     * @param ProductMediaInterface $media     ProductMediaInterface entity
     * @param string                $filename  Filename
     * @param boolean               $overwrite Overwrite file or not
     */
    protected function upload(ProductMediaInterface $media, $filename, $overwrite = false)
    {
        if (($file = $media->getFile())) {
            if ($file instanceof UploadedFile && UPLOAD_ERR_OK !== $file->getError()) {
                return;
            }

            $pathname = $file->getPathname();
            $this->write($filename, file_get_contents($pathname), $overwrite);

            $originalFilename = $file instanceof UploadedFile ?  $file->getClientOriginalName() : $file->getFilename();

            $media->setOriginalFilename($originalFilename);
            $media->setFilename($filename);
            $media->setFilePath($this->getFilePath($media));
            $media->setMimeType($file->getMimeType());
            $media->resetFile();
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
     * Get the file path of a media
     *
     * @param ProductMediaInterface $media
     *
     * @return string|null the path of the media or null if the media has no file attached
     *
     * @throws FileNotFoundException in case the file of the media does not exist or is not readable
     */
    protected function getFilePath(ProductMediaInterface $media)
    {
        if ($this->fileExists($media)) {
            $path = $this->uploadDirectory . DIRECTORY_SEPARATOR . $media->getFilename();
            if (!is_readable($path)) {
                throw new FileNotFoundException($path);
            }

            return $path;
        }

        return null;
    }

    /**
     * Delete a file
     *
     * @param ProductMediaInterface $media
     */
    protected function delete(ProductMediaInterface $media)
    {
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setFilepath(null);
        $media->setMimeType(null);
    }

    /**
     * Predicate to know if file exists physically
     *
     * @param ProductMediaInterface $media
     *
     * @return boolean
     */
    protected function fileExists(ProductMediaInterface $media)
    {
        return $this->filesystem->has($media->getFilename());
    }
}
