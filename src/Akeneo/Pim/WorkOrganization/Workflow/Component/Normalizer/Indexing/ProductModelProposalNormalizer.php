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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a product model proposal to the "indexing_product_model_proposal" format.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX = 'indexing_product_and_product_model_proposal';
    private const FIELD_DOCUMENT_TYPE = 'document_type';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    public function __construct(NormalizerInterface $propertiesNormalizer)
    {
        $this->propertiesNormalizer = $propertiesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productProposal, $format = null, array $context = []): array
    {
        $data = $this->propertiesNormalizer->normalize($productProposal, $format, $context);

        $data[self::FIELD_DOCUMENT_TYPE] = ProductModelDraft::class;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelDraft && self::INDEXING_FORMAT_PRODUCT_MODEL_PROPOSAL_INDEX === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
