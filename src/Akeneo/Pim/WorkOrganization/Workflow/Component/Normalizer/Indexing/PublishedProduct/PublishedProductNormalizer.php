<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    const FIELD_COMPLETENESS = 'completeness';
    const FIELD_IN_GROUP = 'in_group';
    const FIELD_ID = 'id';

    public const INDEXING_FORMAT_PRODUCT_INDEX = 'indexing_product_and_product_model';

    private const FIELD_ANCESTORS = 'ancestors';

    /** @var GetPublishedProductCompletenesses */
    private $getPublishedProductCompletenesses;

    public function __construct(GetPublishedProductCompletenesses $getPublishedProductCompletenesses)
    {
        $this->getPublishedProductCompletenesses = $getPublishedProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context['is_workflow'] = true;

        $data = [];

        $data[self::FIELD_ID] = (string)$publishedProduct->getId();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = $publishedProduct->getIdentifier();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->normalizer->normalize(
            $publishedProduct->getCreated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_UPDATED] = $this->normalizer->normalize(
            $publishedProduct->getUpdated(),
            $format
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = $this->normalizer->normalize(
            $publishedProduct->getFamily(),
            $format
        );

        $data[StandardPropertiesNormalizer::FIELD_ENABLED] = (bool)$publishedProduct->isEnabled();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $publishedProduct->getCategoryCodes();

        $data[StandardPropertiesNormalizer::FIELD_GROUPS] = $publishedProduct->getGroupCodes();

        foreach ($publishedProduct->getGroupCodes() as $groupCode) {
            $data[self::FIELD_IN_GROUP][$groupCode] = true;
        }

        $completenesses = $this->getPublishedProductCompletenesses->fromPublishedProductId($publishedProduct->getId());
        $data[self::FIELD_COMPLETENESS] = $this->normalizer->normalize(
            $completenesses,
            self::INDEXING_FORMAT_PRODUCT_INDEX,
            $context
        );

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$publishedProduct->getValues()->isEmpty()
            ? $this->normalizer->normalize(
                $publishedProduct->getValues(),
                self::INDEXING_FORMAT_PRODUCT_INDEX,
                $context
            ) : [];

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $data[StandardPropertiesNormalizer::FIELD_VALUES],
            $publishedProduct
        );

        $data[self::FIELD_ANCESTORS] = [
            'ids' => [],
            'codes' => [],
        ];

        return $data;
    }

    private function getLabel(array $values, PublishedProductInterface $publishedProduct): array
    {
        if (null === $publishedProduct->getFamily()) {
            return [];
        }

        $attributeAsLabel = $publishedProduct->getFamily()->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return [];
        }

        $valuePath = sprintf('%s-text', $attributeAsLabel->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductInterface && self::INDEXING_FORMAT_PRODUCT_INDEX === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
