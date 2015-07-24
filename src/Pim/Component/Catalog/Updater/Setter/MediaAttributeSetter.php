<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Sets a media value in many products
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetter extends AbstractAttributeSetter
{
    /** @var RawFileStorerInterface */
    protected $storer;

    /** @var FileRepositoryInterface */
    protected $repository;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param RawFileStorerInterface   $storer
     * @param FileRepositoryInterface  $repository
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        RawFileStorerInterface $storer,
        FileRepositoryInterface $repository,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->storer         = $storer;
        $this->repository     = $repository;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "originalFilename": "original_filename.extension",
     *     "filePath": "/absolute/file/path/filename.extension"
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

        if (null === $data || empty($data['filePath'])) {
            $file = null;
        } elseif(null === $file = $this->repository->findOneByIdentifier($data['filePath'])) {
            $file = $this->storeFile($attribute, $data);
        }

        $this->setMedia($product, $attribute, $file, $options['locale'], $options['scope']);
    }

    /**
     * Set media in the product value
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param FileInterface|null $file
     * @param string|null        $locale
     * @param string|null        $scope
     */
    protected function setMedia(
        ProductInterface $product,
        AttributeInterface $attribute,
        FileInterface $file = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        $value->setMedia($file);
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
     * TODO: inform the user that this could take some time
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws \Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException If an invalid filePath is provided
     * @return FileInterface|null
     */
    protected function storeFile(AttributeInterface $attribute, $data)
    {
        if (null === $data || (null === $data['filePath'] && null === $data['originalFilename'])) {
            return null;
        }

        try {
            //TODO: find another way
            $rawFile = new UploadedFile($data['filePath'], $data['originalFilename']);
            //TODO: do not hardcode storage
            $file = $this->storer->store($rawFile, 'storage', false);
        } catch (FileNotFoundException $e) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                'a valid pathname',
                'setter',
                'media',
                $data['filePath']
            );
        }

        return $file;
    }
}
