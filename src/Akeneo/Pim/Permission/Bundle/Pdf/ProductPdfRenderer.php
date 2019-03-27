<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Pdf;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Builder\PdfBuilderInterface;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer as PimProductPdfRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\FilterProductValuesHelper;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * PDF renderer used to render PDF for a Product
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductPdfRenderer extends PimProductPdfRenderer
{
    private const IMAGE_MIME_TYPE_PREFIX = 'image/';

    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;
    /** @var AssetRepositoryInterface|null */
    private $assetRepository;

    // TODO on 3.1: remove null values for arguments
    public function __construct(
        EngineInterface $templating,
        PdfBuilderInterface $pdfBuilder,
        FilterProductValuesHelper $filterHelper,
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        string $template,
        string $uploadDirectory,
        ?string $customFont = null,
        ?AssetRepositoryInterface $assetRepository = null
    ) {
        parent::__construct(
            $templating,
            $pdfBuilder,
            $dataManager,
            $cacheManager,
            $filterManager,
            $attributeRepository,
            $template,
            $uploadDirectory,
            $customFont
        );

        $this->filterHelper = $filterHelper;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributes(ProductInterface $product, $localeCode)
    {
        $values = $this->filterHelper->filter($product->getValues()->toArray(), $localeCode);
        $attributes = [];

        foreach ($values as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            if ($attribute !== null) {
                $attributes[$value->getAttributeCode()] = $attribute;
            }
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

        // TODO on 3.1: remove this test
        if (null === $this->assetRepository) {
            return $imagePaths;
        }

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
                    foreach ($assets as $assetCode) {
                        $asset = $this->assetRepository->findOneByIdentifier($assetCode);
                        $file = $asset->getFileForContext($channel, $locale);

                        if (null !== $file && $this->isImage($file)) {
                            $imagePaths[] = $file->getKey();
                        }
                    }
                }
            }
        }

        return $imagePaths;
    }

    /**
     * Checks a file has a mime type of type image.
     *
     * @param FileInfoInterface $file
     *
     * @return bool
     */
    private function isImage(FileInfoInterface $file): bool
    {
        $fileMimeType = $file->getMimeType();

        return 0 === strpos($fileMimeType, self::IMAGE_MIME_TYPE_PREFIX);
    }
}
