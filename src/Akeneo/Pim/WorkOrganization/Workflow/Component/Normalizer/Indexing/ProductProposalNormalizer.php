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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a product proposal to the "indexing_product_and_product_model_proposal" format.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX = 'indexing_product_and_product_model_proposal';
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

        $data[self::FIELD_DOCUMENT_TYPE] = ProductDraft::class;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductDraft && self::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
