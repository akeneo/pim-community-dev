<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetElasticsearchProductProjection implements GetElasticsearchProductProjectionInterface
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

    /** @var GetAdditionalPropertiesForProductProjectionInterface[] */
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
        $this->additionalDataProviders = $additionalDataProviders;
    }

    public function fromProductIdentifier(string $productIdentifier): ElasticsearchProductProjection
    {
        return $this->fromProductIdentifiers([$productIdentifier])[$productIdentifier];
    }

    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productProjections = [];
        foreach ($productIdentifiers as $productIdentifier) {
            $product = $this->productRepository->findOneByIdentifier($productIdentifier);
            if (null === $product) {
                throw new ObjectNotFoundException(sprintf('Product with identifier "%s" was not found', $productIdentifier));
            }

            $productProjections[$productIdentifier] = ElasticsearchProductProjection::fromProductReadModel(
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
        }

        foreach ($this->additionalDataProviders as $additionalDataProvider) {
            $additionalDataPerProduct = $additionalDataProvider->fromProductIdentifiers($productIdentifiers);
            foreach ($additionalDataPerProduct as $productIdentifier => $additionalData) {
                $productProjections[$productIdentifier] = $productProjections[$productIdentifier]->addAdditionalData($additionalData);
            }
        }

        return $productProjections;
    }
}
