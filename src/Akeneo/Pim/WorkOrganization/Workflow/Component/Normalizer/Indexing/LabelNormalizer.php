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
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a text (simple text) product value
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class LabelNormalizer implements NormalizerInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        if (! $data instanceof ValueInterface) {
            return false;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($data->getAttributeCode());

        return  null !== $attribute && AttributeTypes::BACKEND_TYPE_TEXT === $attribute->getBackendType() && (
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
        $locale = (null === $proposalValue->getLocaleCode()) ? '<all_locales>' : $proposalValue->getLocaleCode();
        $channel = (null === $proposalValue->getScopeCode()) ? '<all_channels>' : $proposalValue->getScopeCode();

        $attribute = $this->attributeRepository->findOneByIdentifier($proposalValue->getAttributeCode());

        if ($attribute !== null) {
            $key = $proposalValue->getAttributeCode() . '-' . $attribute->getBackendType();
            $structure = [];
            $structure[$key][$channel][$locale] = $this->getNormalizedData($proposalValue);

            return $structure;
        } else {
            return null;
        }
    }
}
