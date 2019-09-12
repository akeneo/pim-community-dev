<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\IndexableProduct;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetIndexableProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductDataForIndexationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIndexableProduct implements GetIndexableProductInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** array */
    private $additionalDataProviders = [];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        NormalizerInterface $normalizer,
        GetProductCompletenesses $getProductCompletenesses,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        iterable $additionalDataProviders = []
    ) {
        $this->productRepository = $productRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->normalizer = $normalizer;
        $this->getProductCompletenesses = $getProductCompletenesses;
        $this->attributesProvider = $attributesProvider;

        foreach ($additionalDataProviders as $additionalDataProvider) {
            $this->addAdditionalDataProvider($additionalDataProvider);
        }
    }

    /**
     * @param GetProductDataForIndexationInterface $additionalDataProvider
     * @return GetIndexableProduct
     */
    public function addAdditionalDataProvider(
        GetProductDataForIndexationInterface $additionalDataProvider
    ): GetIndexableProduct {
        $this->additionalDataProviders[] = $additionalDataProvider;

        return $this;
    }

    /**
     * @param string $productIdentifier
     * @return IndexableProduct|null
     */
    public function fromProductIdentifier(string $productIdentifier): ?IndexableProduct
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);
        if (null === $product) {
            return null;
        }

        $indexableProduct = IndexableProduct::fromProductReadModel(
            $product,
            $this->localeRepository->getActivatedLocaleCodes(),
            $this->channelRepository->getChannelCodes(),
            $this->normalizer->normalize(
                $product->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            ),
            $this->getProductCompletenesses->fromProductId($product->getId()),
            $this->attributesProvider
        );

        /**  @var GetProductDataForIndexationInterface $additionalDataProvider */
        foreach ($this->additionalDataProviders as $additionalDataProvider) {
            $indexableProduct->addAdditionalData($additionalDataProvider->fromProductIdentifier($productIdentifier));
        }

        return $indexableProduct;
    }
}
