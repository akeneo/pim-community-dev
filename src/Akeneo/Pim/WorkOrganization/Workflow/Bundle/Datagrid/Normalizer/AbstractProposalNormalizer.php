<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $datagridNormalizer;
    private ProposalChangesNormalizer $changesNormalizer;

    public function __construct(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer
    ) {
        $this->datagridNormalizer = $datagridNormalizer;
        $this->changesNormalizer = $changesNormalizer;
    }

    abstract public function supportsNormalization($data, $format = null): bool;

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        $data = [];
        $product = $proposalProduct->getEntityWithValue();

        $data['proposal_id'] = $proposalProduct->getId();
        $data['createdAt'] = $this->datagridNormalizer->normalize($proposalProduct->getCreatedAt(), $format, $context);
        $data['author_label'] = $proposalProduct->getAuthorLabel();
        $data['document_id'] = $product->getId();
        $data['document_label'] = $product->getLabel();
        $data['formatted_changes'] = $this->changesNormalizer->normalize($proposalProduct, $context);

        return $data;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
