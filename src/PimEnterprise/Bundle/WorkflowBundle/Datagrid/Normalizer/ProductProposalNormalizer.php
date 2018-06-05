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

namespace PimEnterprise\Bundle\WorkflowBundle\Datagrid\Normalizer;

use PimEnterprise\Component\Workflow\Model\ProductDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product normalizer for datagrid
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductProposalNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];

        $data['changes'] = $this->normalizer->normalize($proposalProduct->getValues(), 'standard', $context);
        $data['createdAt'] = $this->normalizer->normalize($proposalProduct->getCreatedAt(), $format, $context);
        $data['product'] = $proposalProduct->getEntityWithValue();
        $data['author'] = $proposalProduct->getAuthor();
        $data['status'] = $proposalProduct->getStatus();
        $data['proposal'] = $proposalProduct;
        $data['search_id'] = $proposalProduct->getEntityWithValue()->getIdentifier();
        $data['id'] = $proposalProduct->getId();
        $data['document_type'] = 'product_draft';

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductDraft && 'datagrid' === $format;
    }
}
