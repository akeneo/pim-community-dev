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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityWithValuesProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $datagridNormalizer;
    private ProposalChangesNormalizer $changesNormalizer;
    private UserContext $userContext;

    public function __construct(
        NormalizerInterface $datagridNormalizer,
        ProposalChangesNormalizer $changesNormalizer,
        UserContext $userContext
    ) {
        $this->datagridNormalizer = $datagridNormalizer;
        $this->changesNormalizer = $changesNormalizer;
        $this->userContext = $userContext;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithValuesDraftInterface && 'datagrid' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entityWithValuesDraft, $format = null, array $context = []): array
    {
        $entityWithValue = $entityWithValuesDraft->getEntityWithValue();

        return [
            'proposal_id' => $entityWithValuesDraft->getId(),
            'createdAt' => $this->datagridNormalizer->normalize($entityWithValuesDraft->getCreatedAt(), $format, $context),
            'author_label' => $entityWithValuesDraft->getAuthorLabel(),
            'author_code' => $entityWithValuesDraft->getAuthor(),
            'document_id' => $entityWithValue->getId(),
            'document_label' => $entityWithValue->getLabel($this->userContext->getUser()->getCatalogLocale()->getCode()),
            'formatted_changes' => $this->changesNormalizer->normalize($entityWithValuesDraft, $context),
            'document_type' => ($entityWithValuesDraft->getEntityWithValue() instanceof ProductModelInterface) ? 'product_model_draft' : 'product_draft',
            'id' => sprintf('%s_%s',
                ($entityWithValuesDraft->getEntityWithValue() instanceof ProductModelInterface) ? 'product_model_draft' : 'product_draft',
                (string) $entityWithValuesDraft->getId()
            ),
        ];
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
