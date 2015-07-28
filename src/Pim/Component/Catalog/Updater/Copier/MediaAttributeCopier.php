<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use League\Flysystem\MountManager;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Copy a media value attribute in other media value attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeCopier extends AbstractAttributeCopier
{
    /** @var RawFileFetcherInterface */
    protected $rawFileFetcher;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var MountManager */
    protected $mountManager;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param RawFileFetcherInterface  $rawFileFetcher
     * @param RawFileStorerInterface   $rawFileStorer
     * @param MountManager             $mountManager
     * @param array                    $supportedFromTypes
     * @param array                    $supportedToTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        RawFileFetcherInterface $rawFileFetcher,
        RawFileStorerInterface $rawFileStorer,
        MountManager $mountManager,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);
        $this->rawFileFetcher     = $rawFileFetcher;
        $this->rawFileStorer      = $rawFileStorer;
        $this->mountManager       = $mountManager;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes   = $supportedToTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope, 'media');
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope, 'media');

        $this->copySingleValue(
            $fromProduct,
            $toProduct,
            $fromAttribute,
            $toAttribute,
            $fromLocale,
            $toLocale,
            $fromScope,
            $toScope
        );
    }

    /**
     * @param ProductInterface   $fromProduct
     * @param ProductInterface   $toProduct
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     */
    protected function copySingleValue(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        $fromLocale,
        $toLocale,
        $fromScope,
        $toScope
    ) {
        $fromValue = $fromProduct->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        if (null !== $fromValue) {
            $toValue = $toProduct->getValue($toAttribute->getCode(), $toLocale, $toScope);
            if (null === $toValue) {
                $toValue = $this->productBuilder->addProductValue($toProduct, $toAttribute, $toLocale, $toScope);
            }

            $filesystem = $this->mountManager->getFilesystem('storage');

            $rawFile = $this->rawFileFetcher->fetch($fromValue->getMedia()->getKey(), $filesystem);
            $file = $this->rawFileStorer->store($rawFile, 'storage', false);

            $toValue->setMedia($file);
            $file->setOriginalFilename($fromValue->getMedia()->getOriginalFilename());
        }
    }
}
