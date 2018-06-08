<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductProposal;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of a product proposal (aka product draft) object to the indexing format.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    const FIELD_ID = 'id';
    const FIELD_PRODUCT_IDENTIFIER = 'product_identifier';
    const FIELD_AUTHOR = 'author';

    /**
     * {@inheritdoc}
     */
    public function normalize($productProposal, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = (string) $productProposal->getId();

        $product = $productProposal->getEntityWithValue();
        $data[self::FIELD_PRODUCT_IDENTIFIER] = $product->getIdentifier();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = (string) $productProposal->getId();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $productProposal->getCreatedAt(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = null !== $product->getFamily() ? $this->serializer->normalize(
            $product->getFamily(),
            ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
        ) : null;
        $data[self::FIELD_AUTHOR] = (string) $productProposal->getAuthor();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $product->getCategoryCodes();
        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$productProposal->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productProposal->getValues(),
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX
            ) : [];

        $attributeAsLabelCode = null !== $product->getFamily() ?
            $product->getFamily()->getAttributeAsLabel()->getCode() : null;

        $productLabel = [];
        if (null !== $attributeAsLabelCode) {
            $labelValue = $product->getValue($attributeAsLabelCode);
            $productLabel = null !== $labelValue
                ? $this->serializer->normalize(
                    $labelValue,
                    $format
                ) : [];
        }

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $productLabel,
            $product
        );

        return $data;
    }

    /**
     * Get product label from family attribute as label product value
     *
     * @param array            $values
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getLabel(array $values, ProductInterface $product): array
    {
        if (null === $product->getFamily()) {
            return [];
        }

        $valuePath = sprintf('%s-text', $product->getFamily()->getAttributeAsLabel()->getCode());
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
        return $data instanceof EntityWithValuesDraftInterface && ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX === $format;
    }
}
