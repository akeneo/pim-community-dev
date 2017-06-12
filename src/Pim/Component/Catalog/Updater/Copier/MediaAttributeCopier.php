<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Copy a media value attribute in other media value attribute
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeCopier extends AbstractAttributeCopier
{
    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param FileFetcherInterface     $fileFetcher
     * @param FileStorerInterface      $fileStorer
     * @param FilesystemProvider       $filesystemProvider
     * @param array                    $supportedFromTypes
     * @param array                    $supportedToTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        FileFetcherInterface $fileFetcher,
        FileStorerInterface $fileStorer,
        FilesystemProvider $filesystemProvider,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->fileFetcher = $fileFetcher;
        $this->fileStorer = $fileStorer;
        $this->filesystemProvider = $filesystemProvider;
        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
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

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

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
     * Copies a single media value and handle the file associated to it.
     *
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
            $file = null;
            if (null !== $fromValue->getData()) {
                $filesystem = $this->filesystemProvider->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
                $rawFile = $this->fileFetcher->fetch($filesystem, $fromValue->getData()->getKey());
                $file = $this->fileStorer->store($rawFile, FileStorage::CATALOG_STORAGE_ALIAS, false);

                $file->setOriginalFilename($fromValue->getData()->getOriginalFilename());
            }

            $this->productBuilder->addOrReplaceProductValue(
                $toProduct,
                $toAttribute,
                $toLocale,
                $toScope,
                null !== $file ? $file->getKey() : null
            );
        }
    }
}
