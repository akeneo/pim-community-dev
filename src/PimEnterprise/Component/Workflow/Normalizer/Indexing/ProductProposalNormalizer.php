<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Normalizer\Indexing;

use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a product proposal to the "indexing_product_proposal" format.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductProposalNormalizer implements NormalizerInterface
{
    public const INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX = 'indexing_product_proposal';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /**
     * @param NormalizerInterface $propertiesNormalizer
     */
    public function __construct(NormalizerInterface $propertiesNormalizer)
    {
        $this->propertiesNormalizer = $propertiesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productProposal, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($productProposal, $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductDraftInterface && self::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX === $format;
    }
}
