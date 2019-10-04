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

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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

    /** @var ValueFactory */
    private $valueFactory;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormlizer,
        ValueFactory $valueFactory,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->datagridNormlizer = $datagridNormlizer;
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        $data = [];

        $data['changes'] = $this->standardNormalizer->normalize(
            $this->getValueCollectionFromChanges($proposalProduct),
            'standard',
            $context
        );
        $data['createdAt'] = $this->datagridNormlizer->normalize($proposalProduct->getCreatedAt(), $format, $context);
        $data['product'] = $proposalProduct->getEntityWithValue();
        $data['author'] = $proposalProduct->getAuthor();
        $data['author_label'] = $proposalProduct->getAuthorLabel();
        $data['source'] = $proposalProduct->getSource();
        $data['source_label'] = $proposalProduct->getSourceLabel();
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

    /**
     * During the fetch of the Draft, the ValueCollectionFactory will remove the empty values. As empty values are
     * filtered in the raw values, deleted values are not rendered properly for the proposal.
     * As the ValueCollectionFactory is used for the Draft too, the $rawValues does not contains empty values anymore.
     * This implies that the proposal are not correctly displayed in the datagrid if you use the $rawValues.
     * So, instead of using the $rawValues, we recalculate the values to display from the $changes field.
     *
     * https://github.com/akeneo/pim-community-dev/issues/10083
     *
     * @param ProductDraft $proposal
     *
     * @return WriteValueCollection
     */
    private function getValueCollectionFromChanges(ProductDraft $proposal): WriteValueCollection
    {
        $changes = $proposal->getChanges();
        $valueCollection = new WriteValueCollection();

        foreach ($changes['values'] as $code => $changeset) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            foreach ($changeset as $index => $change) {
                if (null === $change['data'] || '' === $change['data'] || [] === $change['data']) {
                    $value = $this->valueFactory->createTemporaryNull(
                        $attribute,
                        $change['scope'],
                        $change['locale']
                    );
                } else {
                    $value = $this->valueFactory->create(
                        $attribute,
                        $change['scope'],
                        $change['locale'],
                        $change['data']
                    );
                }
                $valueCollection->add($value);
            }
        }

        return $valueCollection;
    }
}
