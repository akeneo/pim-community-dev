<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PublishedProductAndModelNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const FIELD_ATTRIBUTES_OF_ANCESTORS = 'attributes_of_ancestors';
    private const FIELD_DOCUMENT_TYPE = 'document_type';
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_for_this_level';
    private const FIELD_COMPLETENESS = 'completeness';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_IN_GROUP = 'in_group';
    private const FIELD_ID = 'id';
    private const FIELD_PARENT = 'parent';
    private const FIELD_ANCESTORS = 'ancestors';
    private const FIELD_CATEGORIES_OF_ANCESTORS = 'categories_of_ancestors';

    /** @var NormalizerInterface[] */
    private $additionalDataNormalizers;

    /** @var GetPublishedProductCompletenesses */
    private $getPublishedProductCompletenesses;

    public function __construct(
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        iterable $additionalDataNormalizers = []
    ) {
        $this->getPublishedProductCompletenesses = $getPublishedProductCompletenesses;
        $this->ensureAdditionalNormalizersAreValid($additionalDataNormalizers);
        $this->additionalDataNormalizers = $additionalDataNormalizers;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = 'product_' . (string)$publishedProduct->getId();
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
        $data[self::FIELD_CATEGORIES_OF_ANCESTORS] = [];

        $data[StandardPropertiesNormalizer::FIELD_GROUPS] = $publishedProduct->getGroupCodes();

        foreach ($publishedProduct->getGroupCodes() as $groupCode) {
            $data[self::FIELD_IN_GROUP][$groupCode] = true;
        }

        $completenesses = $this->getPublishedProductCompletenesses->fromPublishedProductId($publishedProduct->getId());
        $data[self::FIELD_COMPLETENESS] = count($completenesses) > 0
            ? $this->normalizer->normalize(
                $completenesses,
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $familyVariantCode = null;
        $data[self::FIELD_FAMILY_VARIANT] = null;
        $data[self::FIELD_PARENT] = null;

        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$publishedProduct->getValues()->isEmpty()
            ? $this->normalizer->normalize(
                $publishedProduct->getValues(),
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
                $context
            ) : [];

        $data[self::FIELD_ANCESTORS] = [];

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $data[StandardPropertiesNormalizer::FIELD_VALUES],
            $publishedProduct
        );

        foreach ($this->additionalDataNormalizers as $normalizer) {
            $data = array_merge($data, $normalizer->normalize($publishedProduct, $format, $context));
        }

        $data[self::FIELD_DOCUMENT_TYPE] = ProductInterface::class;
        $data[self::FIELD_ATTRIBUTES_OF_ANCESTORS] = [];
        $data[self::FIELD_ATTRIBUTES_IN_LEVEL] = $this->getAttributeCodesForOwnLevel($publishedProduct);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format &&
            $data instanceof PublishedProductInterface;
    }

    private function getAttributeCodesForOwnLevel(PublishedProductInterface $publishedProduct): array
    {
        $attributeCodes = array_keys($publishedProduct->getRawValues());
        $familyAttributesCodes = [];
        if (null !== $publishedProduct->getFamily()) {
            $familyAttributesCodes = $publishedProduct->getFamily()->getAttributeCodes();
        }

        $attributeCodes = array_unique(array_merge($familyAttributesCodes, $attributeCodes));
        sort($attributeCodes);

        return $attributeCodes;
    }

    private function getLabel(array $values, PublishedProductInterface $publishedProduct): array
    {
        $family = $publishedProduct->getFamily();
        if (null === $family || null === $family->getAttributeAsLabel()) {
            return [];
        }

        $valuePath = sprintf('%s-text', $family->getAttributeAsLabel()->getCode());
        if (!isset($values[$valuePath])) {
            return [];
        }

        return $values[$valuePath];
    }

    private function ensureAdditionalNormalizersAreValid(iterable $additionalNormalizers): void
    {
        foreach ($additionalNormalizers as $normalizer) {
            if (!$normalizer instanceof NormalizerInterface) {
                throw new \InvalidArgumentException('$normalizer is not a Normalizer');
            }
        }
    }
}
