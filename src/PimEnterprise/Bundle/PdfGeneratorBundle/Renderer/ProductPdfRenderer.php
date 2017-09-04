<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Renderer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Pim\Bundle\PdfGeneratorBundle\Builder\PdfBuilderInterface;
use Pim\Bundle\PdfGeneratorBundle\Renderer\ProductPdfRenderer as PimProductPdfRenderer;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /**
     * @param EngineInterface           $templating
     * @param PdfBuilderInterface       $pdfBuilder
     * @param FilterProductValuesHelper $filterHelper
     * @param DataManager               $dataManager
     * @param CacheManager              $cacheManager
     * @param FilterManager             $filterManager
     * @param string                    $template
     * @param string                    $uploadDirectory
     * @param string|null               $customFont
     */
    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        $template,
        $uploadDirectory,
        $customFont = null
    ) {
        parent::__construct(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $template,
            $uploadDirectory,
            $customFont
        );

        $this->filterHelper = $filterHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $locale)
    {
        $values = $this->filterHelper->filter($product->getValues()->toArray(), $locale);
        $attributes = [];

        foreach ($values as $value) {
            $attributes[$value->getAttribute()->getCode()] = $value->getAttribute();
        }

        return $attributes;
    }

    /**
     * Adds image paths to display for assets.
     *
     * {@inheritdoc}
     */
    protected function getImagePaths(ProductInterface $product, $locale, $scope)
    {
        $imagePaths = parent::getImagePaths($product, $locale, $scope);

        foreach ($this->getAttributes($product, $locale) as $attribute) {
            if (AttributeTypes::ASSETS_COLLECTION === $attribute->getType()) {
                $assets = $product->getValue($attribute->getCode(), $locale, $scope)->getAssets();

                // TODO: To be reworked on master
                // We could use PimEnterprise\Component\ProductAsset\Model\AssetInterface::getFileForContext but it
                // implies to inject locale and channel repositories.
                foreach ($assets as $asset) {
                    if (!$asset->isLocalizable()) {
                        $reference = $asset->getReferences()[0];
                    } else {
                        foreach ($asset->getReferences() as $assetReference) {
                            if ($locale === $assetReference->getLocale()->getCode()) {
                                $reference = $assetReference;
                                break;
                            }
                        }
                    }

                    if (null === $reference) {
                        continue;
                    }

                    foreach ($reference->getVariations() as $variation) {
                        if ($scope === $variation->getChannel()->getCode() &&
                            null !== $variation->getFileInfo() &&
                            null !== $variation->getFileInfo()->getKey()
                        ) {
                            $imagePaths[] = $variation->getFileInfo()->getKey();
                        }
                    }
                }
            }
        }

        return $imagePaths;
    }
}
