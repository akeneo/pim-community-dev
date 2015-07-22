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
     *
     * @throws MediaManagementException
     * @throws \Exception
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
            } elseif ($media->isRemoved()) {
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
        if (null === $path = $this->getFilePath($source)) {
            throw new \LogicException('File path should not be null');
        }

        $target->setFile(new File($path));
        $filename = $this->generateFilename($source->getOriginalFilename(), $filenamePrefix);
        $this->upload($target, $filename);
        $target->setOriginalFilename($source->getOriginalFilename());
    }

    /**
     * @param ProductMediaInterface $media
     * @param string                $targetDir
     *
     * @return bool true on success, false on failure
     */
    public function copy(ProductMediaInterface $media, $targetDir)
    {
        try {
            $path = $this->getFilePath($media);
        } catch (FileNotFoundException $e) {
            return false;
        }

        $targetDir = sprintf('%s/%s', $targetDir, $this->getExportPath($media));

        if (!is_dir(dirname($targetDir))) {
            mkdir(dirname($targetDir), 0777, true);
        }

        return copy($path, $targetDir);
    }

    /**
     * Create a media and load file information
     *
     * @param string  $filename
     * @param boolean $isUploaded
     *
     * @throws \InvalidArgumentException When file does not exist
     *
     * @return ProductMediaInterface
     */
    public function createFromFilename($filename, $isUploaded = true)
    {
        if ($isUploaded) {
            $filePath = $this->uploadDirectory . DIRECTORY_SEPARATOR . $filename;
        } else {
            $filePath = $filename;
        }

        if ($isUploaded && !$this->filesystem->has($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $filePath));
        }
        $media = $this->factory->createMedia();
        $media->setOriginalFilename($filename);
        $media->setFilename($filename);

        if (!$isUploaded) {
            $media->setFile(new File($filename));
        } else {
            $media->setMimeType($this->filesystem->mimeType($filename));
        }

        return $media;
    }

    /**
     * Create a media from file path
     *
     * @param string $filePath
     *
     * @return ProductMediaInterface
     */
    public function createFromFilePath($filePath)
    {
        $media    = $this->factory->createMedia();
        $fileName = pathinfo($filePath, PATHINFO_BASENAME);

        if ('' !== $fileName) {
            $media->setFilename(pathinfo($filePath, PATHINFO_BASENAME));
        }

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
     * @param string                $identifier Can be used to override the default identifier
     *
     * @return string
     */
    public function getExportPath(ProductMediaInterface $media, $identifier = null)
    {
        if (null === $this->getFilePath($media)) {
            return '';
        }

        $value     = $media->getValue();
        $attribute = $value->getAttribute();

        $identifier = null !== $identifier ? $identifier : $value->getEntity()->getIdentifier();
        $target = sprintf('files/%s/%s', $identifier, $attribute->getCode());

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
     * @throws FileNotFoundException in case the file of the media does not exist or is not readable
     *
     * @return string|null the base 64 representation of the file media or null if the media has no file attached
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
     * @throws FileNotFoundException in case the file of the media does not exist or is not readable
     *
     * @return string|null the path of the media or null if the media has no file attached
     */
    public function getFilePath(ProductMediaInterface $media)
    {
        if (null === $media->getFilename()) {
            return null;
        }

        if (!$this->fileExists($media)) {
            throw new FileNotFoundException($media->getFilename());
        }

        $path = $this->uploadDirectory . DIRECTORY_SEPARATOR . $media->getFilename();
        if (!is_readable($path)) {
            throw new FileNotFoundException($path);
        }

        return $path;
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
        if (null === $media->getFilename()) {
            return false;
        }

        return $this->filesystem->has($media->getFilename());
    }
}
