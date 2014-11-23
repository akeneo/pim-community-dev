<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;
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
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var MediaManager */
    protected $mediaManager;

    /** @var MediaFactory */
    protected $mediaFactory;

    /**
     * @param ProductBuilderInterface $builder
     * @param MediaManager            $manager
     * @param MediaFactory            $mediaFactory
     * @param array                   $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $builder,
        MediaManager $manager,
        MediaFactory $mediaFactory,
        array $supportedTypes
    ) {
        $this->productBuilder = $builder;
        $this->mediaManager   = $manager;
        $this->mediaFactory   = $mediaFactory;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        $this->checkData($attribute, $data);

        try {
            $file = new UploadedFile($data['filePath'], $data['originalFilename']);
        } catch (FileNotFoundException $e) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                sprintf('a valid file path ("%s" given)', $data['filePath']),
                'setter',
                'media'
            );
        }

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
     * @param UploadedFile       $file
     * @param string             $locale
     * @param string             $scope
     */
    protected function setMedia(
        AttributeInterface $attribute,
        ProductInterface $product,
        UploadedFile $file,
        $locale,
        $scope
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null === $media = $value->getMedia()) {
            $media = $this->mediaFactory->createMedia($file);
        } else {
            $media->setFile($file);
        }
        $value->setMedia($media);
    }
    
    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'setter', 'media');
        }

        if (!array_key_exists('originalFilename', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'originalFilename',
                'setter',
                'media'
            );
        }

        if (!array_key_exists('filePath', $data)) {
            throw InvalidArgumentException::arrayKeyExpected($attribute->getCode(), 'filePath', 'setter', 'media');
        }
    }
}
