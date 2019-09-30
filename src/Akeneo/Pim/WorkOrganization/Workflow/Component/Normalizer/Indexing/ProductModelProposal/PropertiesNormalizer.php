<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposal;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PropertiesNormalizer as StandardPropertiesNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\ProductModelProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Transform the properties of a product model proposal (aka product model draft) object to the indexing format.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class PropertiesNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    const FIELD_ID = 'id';
    const FIELD_ENTITY_WITH_VALUES_IDENTIFIER = 'entity_with_values_identifier';
    const FIELD_AUTHOR = 'author';

    /**
     * {@inheritdoc}
     */
    public function normalize($productModelProposal, $format = null, array $context = []): array
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data[self::FIELD_ID] = 'product_model_draft_' . (string) $productModelProposal->getId();

        $productModel = $productModelProposal->getEntityWithValue();
        $data[self::FIELD_ENTITY_WITH_VALUES_IDENTIFIER] = $productModel->getCode();
        $data[StandardPropertiesNormalizer::FIELD_IDENTIFIER] = (string) $productModelProposal->getId();
        $data[StandardPropertiesNormalizer::FIELD_CREATED] = $this->serializer->normalize(
            $productModelProposal->getCreatedAt(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        );
        $data[StandardPropertiesNormalizer::FIELD_FAMILY] = null !== $productModel->getFamily() ? $this->serializer->normalize(
            $productModel->getFamily(),
            ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        ) : null;
        $data[self::FIELD_AUTHOR] = (string) $productModelProposal->getAuthor();
        $data[StandardPropertiesNormalizer::FIELD_CATEGORIES] = $productModel->getCategoryCodes();
        $data[StandardPropertiesNormalizer::FIELD_VALUES] = !$productModelProposal->getValues()->isEmpty()
            ? $this->serializer->normalize(
                $productModelProposal->getValues(),
                ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            ) : [];

        $attributeAsLabel = $productModel->getFamily() ? $productModel->getFamily()->getAttributeAsLabel() : null;
        $attributeAsLabelCode = null !== $attributeAsLabel ? $attributeAsLabel->getCode() : null;

        $labelValue = $productModel->getValue($attributeAsLabelCode);

        $productLabel = null !== $labelValue
            ? $this->serializer->normalize(
                $labelValue,
                $format
            ) : [];

        $data[StandardPropertiesNormalizer::FIELD_LABEL] = $this->getLabel(
            $productLabel,
            $productModel
        );

        return $data;
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

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelDraft && ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX === $format;
    }
}
