<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transform the properties of products and variant product objects (fields and product values)
 * to the "indexing_product_and_product_model" format.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPropertiesNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private const FIELD_COMPLETENESS = 'completeness';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_IN_GROUP = 'in_group';
    private const FIELD_ID = 'id';
    private const FIELD_PARENT = 'parent';
    private const FIELD_ANCESTORS = 'ancestors';
    private const FIELD_CATEGORIES_OF_ANCESTORS = 'categories_of_ancestors';

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var NormalizerInterface[] */
    private $additionalDataNormalizers;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        GetProductCompletenesses $getProductCompletenesses,
        NormalizerInterface $normalizer,
        iterable $additionalDataNormalizers = []
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->getProductCompletenesses = $getProductCompletenesses;
        $this->normalizer = $normalizer;

        $this->ensureAdditionalNormalizersAreValid($additionalDataNormalizers);
        $this->additionalDataNormalizers = $additionalDataNormalizers;
    }

    /**
     * {@inheritdoc}
     *
     * @var ProductInterface $product
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = 'product_' .(string) $product->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $product->getIdentifier();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->normalizer->normalize(
            $product->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->normalizer->normalize(
            $this->getUpdatedAt($product),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $this->normalizer->normalize(
            $product->getFamily(),
            $format
        );

        $data[StandardPropertiesNormalizer::FIELD_ENABLED] = (bool) $product->isEnabled();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $product->getCategoryCodes();
        $ancestorsCategories = [];
        if ($product->isVariant() && null !== $product->getParent()) {
            $ancestorsCategories = $product->getParent()->getCategoryCodes();
        }
        $data[self::FIELD_CATEGORIES_OF_ANCESTORS] = $ancestorsCategories;

        $data[StandardPropertiesNormalizer::FIELD_GROUPS] = $product->getGroupCodes();

        foreach ($product->getGroupCodes() as $groupCode) {
            $data[self::FIELD_IN_GROUP][$groupCode] = true;
        }

        $completenesses = $this->getProductCompletenesses->fromProductId($product->getId());
        $data[self::FIELD_COMPLETENESS] = $this->normalizer->normalize(
            $completenesses,
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            $context
        );

        $familyVariantCode = null;
        if ($product->isVariant()) {
            $familyVariant = $product->getFamilyVariant();
            $familyVariantCode = null !== $familyVariant ? $familyVariant->getCode() : null;
        }
        $data[self::FIELD_FAMILY_VARIANT] = $familyVariantCode;

        $parentCode = null;
        if ($product->isVariant() && null !== $product->getParent()) {
            $parentCode = $product->getParent()->getCode();
        }
        $data[self::FIELD_PARENT] = $parentCode;

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$product->getValues()->isEmpty()
            ? $this->normalizer->normalize(
                $product->getValues(),
                ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $data[self::FIELD_ANCESTORS] = $this->getAncestors($product);

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $data[StandardPropertiesNormalizer::FIELD_VALUES],
            $product
        );

        foreach ($this->additionalDataNormalizers as $normalizer) {
            $data = array_merge($data, $normalizer->normalize($product, $format, $context));
        }

        return $data;
    }

    /**
     * Get label of the given product
     *
     * @param array            $values
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getLabel(array $values, ProductInterface $product): array
    {
        $family = $product->getFamily();
        if (null === $family || null === $family->getAttributeAsLabel()) {
            return [];
        }

        $valuePath = sprintf('%s-text', $family->getAttributeAsLabel()->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface
            && ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface $product
     *
     * @return array
     */
    private function getAncestors(ProductInterface $product): array
    {
        $ancestorsIds = [];
        $ancestorsCodes = [];
        $ancestorsLabels = [];
        if ($product->isVariant()) {
            $ancestorsIds = $this->getAncestorsIds($product);
            $ancestorsCodes = $this->getAncestorsCodes($product);
            $ancestorsLabels = $this->getAncestorsLabels($product);
        }

        $ancestors = [
            'ids' => $ancestorsIds,
            'codes' => $ancestorsCodes,
            'labels' => $ancestorsLabels,
        ];

        return $ancestors;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return array
     */
    private function getAncestorsIds(EntityWithFamilyVariantInterface $entityWithFamilyVariant): array
    {
        $ancestorsIds = [];
        while (null !== $parent = $entityWithFamilyVariant->getParent()) {
            $ancestorsIds[] = 'product_model_' . $parent->getId();
            $entityWithFamilyVariant = $parent;
        }

        return $ancestorsIds;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     *
     * @return array
     */
    private function getAncestorsCodes(EntityWithFamilyVariantInterface $entityWithFamilyVariant)
    {
        $ancestorsCodes = [];
        while (null !== $parent = $entityWithFamilyVariant->getParent()) {
            $ancestorsCodes[] = $parent->getCode();
            $entityWithFamilyVariant = $parent;
        }

        return $ancestorsCodes;
    }

    /**
     * Retrieves ancestors labels for each locales and channels.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return array
     */
    private function getAncestorsLabels(EntityWithFamilyVariantInterface $entity): array
    {
        $family = $entity->getFamily();
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
                $ancestorsLabels = $this->getLocalizableAndScopableLabels($entity, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isScopable():
                $ancestorsLabels = $this->getScopableLabels($entity, $attributeCodeAsLabel);
                break;

            case $attributeAsLabel->isLocalizable():
                $ancestorsLabels = $this->getLocalizableLabels($entity, $attributeCodeAsLabel);
                break;

            default:
                $value = $entity->getValue($attributeCodeAsLabel);
                if (null !== $value) {
                    $ancestorsLabels['<all_channels>']['<all_locales>'] = $value->getData();
                }
                break;
        }

        return $ancestorsLabels;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getLocalizableAndScopableLabels(
        EntityWithFamilyVariantInterface $entity,
        string $attributeCodeAsLabel
    ): array {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            foreach ($localeCodes as $localeCode) {
                $value = $entity->getValue($attributeCodeAsLabel, $localeCode, $channelCode);
                if (null !== $value) {
                    $ancestorsLabels[$channelCode][$localeCode] = $value->getData();
                }
            }
        }

        return $ancestorsLabels;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getScopableLabels(EntityWithFamilyVariantInterface $entity, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        foreach ($this->channelRepository->getChannelCodes() as $channelCode) {
            $value = $entity->getValue($attributeCodeAsLabel, null, $channelCode);
            if (null !== $value) {
                $ancestorsLabels[$channelCode]['<all_locales>'] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param string $attributeCodeAsLabel
     *
     * @return array
     */
    private function getLocalizableLabels(EntityWithFamilyVariantInterface $entity, string $attributeCodeAsLabel): array
    {
        $ancestorsLabels = [];
        $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        foreach ($localeCodes as $localeCode) {
            $value = $entity->getValue($attributeCodeAsLabel, $localeCode);
            if (null !== $value) {
                $ancestorsLabels['<all_channels>'][$localeCode] = $value->getData();
            }
        }

        return $ancestorsLabels;
    }

    private function ensureAdditionalNormalizersAreValid(iterable $additionalNormalizers): void
    {
        foreach ($additionalNormalizers as $normalizer) {
            if (! $normalizer instanceof NormalizerInterface) {
                throw new \InvalidArgumentException('$normalizer is not a Normalizer');
            }
        }
    }

    private function getUpdatedAt(ProductInterface $product): \DateTime
    {
        $date = $product->getUpdated();
        if ($product->isVariant()) {
            $dates = [$date];
            $parent = $product->getParent();
            while (null !== $parent) {
                $dates[] = $parent->getUpdated();
                $parent = $parent->getParent();
            }

            $date = max($dates);
        }

        return $date;
    }
}
