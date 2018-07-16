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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product model normalizer for datagrid
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelProposalNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalModelProduct, $format = null, array $context = []): array
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data['changes'] = $this->normalizer->normalize($proposalModelProduct->getValues(), 'standard', $context);
        $data['createdAt'] = $this->normalizer->normalize($proposalModelProduct->getCreatedAt(), $format, $context);
        $data['product'] = $proposalModelProduct->getEntityWithValue();
        $data['author'] = $proposalModelProduct->getAuthor();
        $data['status'] = $proposalModelProduct->getStatus();
        $data['proposal'] = $proposalModelProduct;
        $data['search_id'] = $proposalModelProduct->getEntityWithValue()->getCode();
        $data['id'] = 'product_model_draft_' . (string) $proposalModelProduct->getId();
        $data['document_type'] = 'product_model_draft';
        $data['proposal_id'] = $proposalModelProduct->getId();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelDraft && 'datagrid' === $format;
    }
}
