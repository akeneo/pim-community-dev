<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CompleteFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetElasticsearchProductModelProjection implements GetElasticsearchProductModelProjectionInterface
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var CompleteFilterInterface */
    private $completenessGridFilterQuery;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $normalizer,
        CompleteFilterInterface $completenessGridFilterQuery,
        ChannelRepositoryInterface $channelRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->localeRepository = $localeRepository;
        $this->normalizer = $normalizer;
        $this->completenessGridFilterQuery = $completenessGridFilterQuery;
        $this->channelRepository = $channelRepository;
        $this->attributesProvider = $attributesProvider;
    }

    public function fromProductModelCodes(array $productModelCodes): array
    {
        $productProjections = [];
        $activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();

        foreach ($productModelCodes as $productModelCode) {
            /** @var ProductModelInterface $productModel */
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);
            if (null === $productModel) {
                throw new ObjectNotFoundException(sprintf('Product model with code "%s" was not found', $productModelCode));
            }

            $familyLabels = [];
            foreach ($activatedLocaleCodes as $activatedLocaleCode) {
                $translation = $productModel->getFamily()->getTranslation($activatedLocaleCode);
                if (null !== $translation) {
                    $familyLabels[$activatedLocaleCode] = $translation->getLabel();
                }
            }

            $normalizedData = $this->completenessGridFilterQuery->findCompleteFilterData($productModel);
            $values = $this->normalizer->normalize(
                $productModel->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );

            $productProjections[$productModelCode] = new ElasticsearchProductModelProjection(
                $productModel->getId(),
                $productModelCode,
                \DateTimeImmutable::createFromMutable($productModel->getCreated()),
                \DateTimeImmutable::createFromMutable($productModel->getUpdated()),
                $productModel->getFamily()->getCode(),
                $familyLabels,
                $productModel->getFamilyVariant()->getCode(),
                $productModel->getCategoryCodes(),
                null !== $productModel->getParent() ? $productModel->getParent()->getCategoryCodes() : [],
                null !== $productModel->getParent() ? $productModel->getParent()->getCode() : null,
                $values,
                $normalizedData->allComplete(),
                $normalizedData->allIncomplete(),
                null !== $productModel->getParent() ? ['product_model_' . $productModel->getParent()->getId()] : [],
                null !== $productModel->getParent() ? [$productModel->getParent()->getCode()] : [],
                $this->getAncestorsLabels($productModel),
                $this->getLabel($values, $productModel),
                $this->getAttributesOfAncestors($productModel),
                $this->getSortedAttributeCodes($productModel)
            );
        }

        return $productProjections;
    }

    private function getAncestorsLabels(ProductModelInterface $productModel): array
    {
        $family = $productModel->getFamily();
        if (null === $family) {
            return [];
        }

        $attributeAsLabel = $family->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $ancestorsLabels = [];
        $attributeCodeAsLabel = $attributeAsLabel->getCode();
        switch (true) {
            case $attributeAsLabel->isScopable() && $attributeAsLabel->isLocalizable():
                $ancestorsLabels = $this->getLocalizableAndScopableLabels($productModel, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isScopable():
                $ancestorsLabels = $this->getScopableLabels($productModel, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isLocalizable():
                $ancestorsLabels = $this->getLocalizableLabels($productModel, $attributeCodeAsLabel);
                break;

            default:
                $value = $productModel->getValue($attributeCodeAsLabel);
                if (null !== $value) {
                    $ancestorsLabels['<all_channels>']['<all_locales>'] = $value->getData();
                }
                break;
        }

        return $ancestorsLabels;
    }

    private function getLocalizableAndScopableLabels(
        ProductModelInterface $productModel,
        string $attributeCodeAsLabel
    ): array {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            foreach ($localeCodes as $localeCode) {
                $value = $productModel->getValue($attributeCodeAsLabel, $localeCode, $channelCode);
                if (null !== $value) {
                    $ancestorsLabels[$channelCode][$localeCode] = $value->getData();
                }
            }
        }

        return $ancestorsLabels;
    }

    private function getScopableLabels(ProductModelInterface $productModel, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            $value = $productModel->getValue($attributeCodeAsLabel, null, $channelCode);
            if (null !== $value) {
                $ancestorsLabels[$channelCode]['<all_locales>'] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    private function getLocalizableLabels(ProductModelInterface $productModel, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $localeCode) {
            $value = $productModel->getValue($attributeCodeAsLabel, $localeCode);
            if (null !== $value) {
                $ancestorsLabels['<all_channels>'][$localeCode] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    private function getLabel(array $values, ProductModelInterface $productModel): array
    {
        if (null === $productModel->getFamily()) {
            return [];
        }

        $attributeAsLabel = $productModel->getFamily()->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $valuePath = sprintf('%s-text', $attributeAsLabel->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    private function getAttributesOfAncestors(ProductModelInterface $productModel): array
    {
        if (null === $productModel->getFamilyVariant()) {
            return [];
        }

        if (ProductModel::ROOT_VARIATION_LEVEL === $productModel->getVariationLevel()) {
            return [];
        }

        $attributesOfAncestors = $productModel->getFamilyVariant()
            ->getCommonAttributes()
            ->map(
                function (AttributeInterface $attribute) {
                    return $attribute->getCode();
                }
            )->toArray();

        sort($attributesOfAncestors);

        return $attributesOfAncestors;
    }

    private function getSortedAttributeCodes(ProductModelInterface $entityWithFamilyVariant): array
    {
        $attributes = $this->attributesProvider->getAttributes($entityWithFamilyVariant);
        $attributeCodes = array_map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        }, $attributes);

        sort($attributeCodes);

        return $attributeCodes;
    }
}
