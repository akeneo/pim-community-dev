<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Copy a media value attribute in other media value attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaValueCopier extends AbstractValueCopier
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var MediaFactory */
    protected $mediaFactory;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param MediaManager             $mediaManager
     * @param MediaFactory             $mediaFactory
     * @param array                    $supportedFromTypes
     * @param array                    $supportedToTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        MediaManager $mediaManager,
        MediaFactory $mediaFactory,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->mediaManager       = $mediaManager;
        $this->mediaFactory       = $mediaFactory;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes   = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope, 'base');
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope, 'base');

        foreach ($products as $product) {
            $this->copySingleValue(
                $product,
                $fromAttribute,
                $toAttribute,
                $fromLocale,
                $toLocale,
                $fromScope,
                $toScope
            );
        }
    }

    /**
     * @param ProductInterface   $product
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     */
    protected function copySingleValue(
        ProductInterface $product,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $product->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $toValue = $product->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($product, $toAttribute, $toLocale, $toScope);
            }

            $mediaHasFileName = false;
            if (null !== $fromValue->getMedia()) {
                $originalFileName = $fromValue->getMedia()->getOriginalFilename();
                if (!empty($originalFileName)) {
                    $mediaHasFileName = true;
                }
            }

            if ($mediaHasFileName) {
                $this->duplicateMedia($product, $fromValue, $toValue);
            } else {
                $this->deleteMedia($toValue);
            }
        }
    }

    /**
     * TODO: remove this method after the refactoring of the product media manager
     *
     * @param ProductValueInterface $toValue
     */
    protected function deleteMedia(ProductValueInterface $toValue)
    {
        if (null !== $media = $toValue->getMedia()) {
            $media->setOriginalFilename(null);
            $media->setFilename(null);
            $media->setMimeType(null);
        }
    }

    /**
     * TODO: remove this method after the refactoring of the product media manager
     *
     * @param ProductInterface      $product
     * @param ProductValueInterface $fromValue
     * @param ProductValueInterface $toValue
     */
    protected function duplicateMedia(
        ProductInterface $product,
        ProductValueInterface $fromValue,
        ProductValueInterface $toValue
    ) {
        if (null === $toValue->getMedia()) {
            $media = $this->mediaFactory->createMedia();
            $toValue->setMedia($media);
        }

        $this->mediaManager->duplicate(
            $fromValue->getMedia(),
            $toValue->getMedia(),
            $this->mediaManager->generateFilenamePrefix($product, $fromValue)
        );
    }
}
