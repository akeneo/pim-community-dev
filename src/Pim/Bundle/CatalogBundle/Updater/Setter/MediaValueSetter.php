<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Sets a media value in many products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaValueSetter extends AbstractValueSetter
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
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'media');
        $this->checkData($attribute, $data);

        $file = $this->getFileData($attribute, $data);

        foreach ($products as $product) {
            $this->setMedia($attribute, $product, $file, $locale, $scope);
        }

        $this->mediaManager->handleAllProductsMedias($products);
    }

    /**
     * Set media in the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param UploadedFile|null  $file
     * @param string|null        $locale
     * @param string|null        $scope
     */
    protected function setMedia(
        AttributeInterface $attribute,
        ProductInterface $product,
        UploadedFile $file = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null === $media = $value->getMedia()) {
            $media = $this->mediaFactory->createMedia($file);
        } else {
            if (null === $file) {
                $media->setRemoved(true);
            } else {
                $media->setFile($file);
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
     * @return UploadedFile|null
     */
    protected function getFileData(AttributeInterface $attribute, $data)
    {
        if (null === $data || (null === $data['filePath'] && null === $data['originalFilename'])) {
            return null;
        }

        $data = $this->resolveFilePath($data);

        try {
            return new UploadedFile($data['filePath'], $data['originalFilename']);
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
