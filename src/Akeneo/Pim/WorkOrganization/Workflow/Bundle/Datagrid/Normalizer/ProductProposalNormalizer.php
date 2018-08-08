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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product normalizer for datagrid
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductProposalNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var NormalizerInterface */
    private $datagridNormlizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param NormalizerInterface $datagridNormlizer
     */
    public function __construct(NormalizerInterface $standardNormalizer, NormalizerInterface $datagridNormlizer)
    {
        $this->standardNormalizer = $standardNormalizer;
        $this->datagridNormlizer = $datagridNormlizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        $data = [];

        $data['changes'] = $this->standardNormalizer->normalize($proposalProduct->getValues(), 'standard', $context);
        $data['createdAt'] = $this->datagridNormlizer->normalize($proposalProduct->getCreatedAt(), $format, $context);
        $data['product'] = $proposalProduct->getEntityWithValue();
        $data['author'] = $proposalProduct->getAuthor();
        $data['status'] = $proposalProduct->getStatus();
        $data['proposal'] = $proposalProduct;
        $data['search_id'] = $proposalProduct->getEntityWithValue()->getIdentifier();
        $data['id'] = 'product_draft_' . (string) $proposalProduct->getId();
        $data['document_type'] = 'product_draft';
        $data['proposal_id'] = $proposalProduct->getId();

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
