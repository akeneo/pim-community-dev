<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Sets a media value in many products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetter extends AbstractAttributeSetter
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var MediaFactory */
    protected $mediaFactory;

    /** @var string */
    protected $uploadDir;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param MediaManager             $manager
     * @param MediaFactory             $mediaFactory
     * @param array                    $supportedTypes
     * @param string                   $uploadDir
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        MediaManager $manager,
        MediaFactory $mediaFactory,
        array $supportedTypes,
        $uploadDir
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->mediaManager   = $manager;
        $this->mediaFactory   = $mediaFactory;
        $this->supportedTypes = $supportedTypes;
        $this->uploadDir      = $uploadDir;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "originalFilename": "original_filename.extension",
     *     "filePath": "/current/file/path/original_filename.extension"
     * }
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'media');
        $this->checkData($attribute, $data);
        $file = $this->getFileData($attribute, $data);
        $this->setMedia($product, $attribute, $file, $data['originalFilename'], $options['locale'], $options['scope']);
        $this->mediaManager->handleProductMedias($product);
    }

    /**
     * Set media in the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param File|null          $file
     * @param string|null        $originalFilename
     * @param string|null        $locale
     * @param string|null        $scope
     */
    protected function setMedia(
        ProductInterface $product,
        AttributeInterface $attribute,
        File $file = null,
        $originalFilename = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null === $media = $value->getMedia()) {
            $media = $this->mediaFactory->createMedia($file);
            $media->setOriginalFilename($originalFilename);
        } else {
            if (null === $file) {
                $media->setRemoved(true);
            } else {
                $media->setFile($file);
                $media->setOriginalFilename($originalFilename);
            }
        }

        $value->setMedia($media);
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'setter', 'media', gettype($data));
        }

        if (!array_key_exists('originalFilename', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'originalFilename',
                'setter',
                'media',
                print_r($data, true)
            );
        }

        if (!array_key_exists('filePath', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'filePath',
                'setter',
                'media',
                print_r($data, true)
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws \Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException If an invalid filePath is provided
     *
     * @return File|null
     */
    protected function getFileData(AttributeInterface $attribute, $data)
    {
        if (null === $data || (null === $data['filePath'] && null === $data['originalFilename'])) {
            return null;
        }

        $data = $this->resolveFilePath($data);

        try {
            return new File($data['filePath']);
        } catch (FileNotFoundException $e) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'a valid file path',
                'setter',
                'media',
                $data['filePath']
            );
        }
    }

    /**
     * Resolve the file path of a media or an image
     *
     * @param array $data
     *
     * @return array
     */
    protected function resolveFilePath(array $data)
    {
        $uploadDir = $this->uploadDir;
        if (file_exists($data['filePath'])) {
            return $data;
        }

        if (substr($uploadDir, -1) !== DIRECTORY_SEPARATOR) {
            $uploadDir = $this->uploadDir.DIRECTORY_SEPARATOR;
        }

        $path  = $uploadDir.$data['filePath'];
        $value = ['filePath' => $path, 'originalFilename' => $data['originalFilename']];

        return $value;
    }
}
