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
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
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

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param EngineInterface            $templating
     * @param PdfBuilderInterface        $pdfBuilder
     * @param FilterProductValuesHelper  $filterHelper
     * @param DataManager                $dataManager
     * @param CacheManager               $cacheManager
     * @param FilterManager              $filterManager
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param string                     $template
     * @param string                     $uploadDirectory
     * @param null                       $customFont
     */
    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
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
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $localeCode)
    {
        $values = $this->filterHelper->filter($product->getValues()->toArray(), $localeCode);
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
    protected function getImagePaths(ProductInterface $product, $localeCode, $scope)
    {
        $imagePaths = parent::getImagePaths($product, $localeCode, $scope);

        $channel = $this->channelRepository->findOneByIdentifier($scope);
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        foreach ($this->getAttributes($product, $localeCode) as $attribute) {
            if (AttributeTypes::ASSETS_COLLECTION === $attribute->getType()) {
                $assetsValue = $product->getValue(
                    $attribute->getCode(),
                    $attribute->isLocalizable() ? $localeCode : null,
                    $attribute->isScopable() ? $scope : null
                );

                if (null !== $assetsValue) {
                    $assets = $assetsValue->getData();
                    foreach ($assets as $asset) {
                        $file = $asset->getFileForContext($channel, $locale);

                        if (null !== $file) {
                            $imagePaths[] = $file->getKey();
                        }
                    }
                }
            }
        }

        return $imagePaths;
    }
}
