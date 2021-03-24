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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product model normalizer for datagrid
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var NormalizerInterface */
    private $datagridNormlizer;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var GetAttributes */
    private $getAttributesQuery;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormlizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->datagridNormlizer = $datagridNormlizer;
        $this->valueFactory = $valueFactory;
        $this->getAttributesQuery = $getAttributesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalModelProduct, $format = null, array $context = []): array
    {
        $data = [];

        $data['changes'] = $this->getChanges($proposalModelProduct, $context);
        $data['createdAt'] = $this->datagridNormlizer->normalize(
            $proposalModelProduct->getCreatedAt(),
            $format,
            $context
        );
        $data['product'] = $proposalModelProduct->getEntityWithValue();
        $data['author'] = $proposalModelProduct->getAuthor();
        $data['author_label'] = $proposalModelProduct->getAuthorLabel();
        $data['source'] = $proposalModelProduct->getSource();
        $data['source_label'] = $proposalModelProduct->getSourceLabel();
        $data['status'] = $proposalModelProduct->getStatus();
        $data['proposal'] = $proposalModelProduct;
        $data['search_id'] = $proposalModelProduct->getEntityWithValue()->getCode();
        $data['id'] = 'product_model_draft_' . (string)$proposalModelProduct->getId();
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function getChanges(ProductModelDraft $proposal, array $context): array
    {
        $normalizedValues = $this->standardNormalizer->normalize(
            $this->getValueCollectionFromChangesWithoutEmptyValues($proposal),
            'standard',
            $context
        );

        $changes = $proposal->getChanges();
        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                if ($this->isChangeDataNull($change['data'])) {
                    $normalizedValues[$code][] = [
                        'data' => null,
                        'locale' => $change['locale'],
                        'scope' => $change['scope'],
                    ];
                }
            }
        }

        return $normalizedValues;
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
     * @param ProductModelDraft $proposal
     *
     * @return WriteValueCollection
     */
    private function getValueCollectionFromChangesWithoutEmptyValues(ProductModelDraft $proposal): WriteValueCollection
    {
        $changes = $proposal->getChanges();
        $valueCollection = new WriteValueCollection();

        foreach ($changes['values'] as $code => $changeset) {
            $attribute = $this->getAttributesQuery->forCode($code);
            foreach ($changeset as $index => $change) {
                if (true === $this->isChangeDataNull($change['data'])) {
                    continue;
                }

                if (false === $this->changeNeedsReview($proposal, $code, $change['locale'], $change['scope'])) {
                    continue;
                }

                $valueCollection->add(
                    $this->valueFactory->createByCheckingData(
                        $attribute,
                        $change['scope'],
                        $change['locale'],
                        $change['data']
                    )
                );
            }
        }

        return $valueCollection;
    }

    private function isChangeDataNull($changeData): bool
    {
        return null === $changeData || '' === $changeData || [] === $changeData;
    }

    private function changeNeedsReview(
        ProductModelDraft $proposal,
        string $code,
        ?string $localeCode,
        ?string $channelCode
    ): bool {
        return EntityWithValuesDraftInterface::CHANGE_TO_REVIEW === $proposal->getReviewStatusForChange($code, $localeCode, $channelCode);
    }
}
