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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Component\Catalog\AttributeTypes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a text (simple text) product value
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class LabelNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ValueInterface &&
            AttributeTypes::BACKEND_TYPE_TEXT === $data->getAttribute()->getBackendType() && (
                $format === ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX ||
                $format === ProductModelProposalNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        return $value->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalValue, $format = null, array $context = []): array
    {
        $locale = (null === $proposalValue->getLocale()) ? '<all_locales>' : $proposalValue->getLocale();
        $channel = (null === $proposalValue->getScope()) ? '<all_channels>' : $proposalValue->getScope();

        $key = $proposalValue->getAttribute()->getCode() . '-' . $proposalValue->getAttribute()->getBackendType();
        $structure = [];
        $structure[$key][$channel][$locale] = $this->getNormalizedData($proposalValue);

        return $structure;
    }
}
