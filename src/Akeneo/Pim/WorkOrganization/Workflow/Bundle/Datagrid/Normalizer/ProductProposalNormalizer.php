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

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftChangesExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftStatusGridExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product normalizer for datagrid
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private NormalizerInterface $standardNormalizer;
    private NormalizerInterface $datagridNormlizer;
    private ValueFactory $valueFactory;
    private GetAttributes $getAttributesQuery;
    private ProductDraftChangesExtension $changesExtension;
    private AuthorizationCheckerInterface $authorizationChecker;
    private AttributeRepositoryInterface $attributeRepository;
    private LocaleRepositoryInterface $localeRepository;
    private ProductDraftStatusGridExtension $statusExtension;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormlizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery,
        ProductDraftChangesExtension $changesExtension,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductDraftStatusGridExtension $statusExtension
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->datagridNormlizer = $datagridNormlizer;
        $this->valueFactory = $valueFactory;
        $this->getAttributesQuery = $getAttributesQuery;
        $this->changesExtension = $changesExtension;
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->statusExtension = $statusExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        /** @var ProductDraft $proposalProduct */
        $data = [];

        /** @var ProductInterface $product */
        $product = $proposalProduct->getEntityWithValue();

        $data['changes'] = $this->getChanges($proposalProduct, $context);
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
        $data['document_id'] = $product->getId();
        $data['proposal_id'] = $proposalProduct->getId();
        $data['document_label'] = $product->getLabel();
        $data['formatted_changes'] = $this->formatChanges($proposalProduct, $context);

        return $data;
    }

    private function formatChanges(ProductDraft $proposalProduct, $context)
    {
        $changesWithEmptyValues = $this->getChanges($proposalProduct, $context);
        if ($proposalProduct->getStatus() === EntityWithValuesDraftInterface::IN_PROGRESS) {
            return [
                'status_label' => $this->statusExtension->getDraftStatusGrid($proposalProduct),
                'status' => 'in_progress',
            ];
        }
        $proposalChanges = [];
        foreach ($changesWithEmptyValues as $attributeCode => $changes) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $canView = $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
            if ($canView) {
                $proposalChanges[$attributeCode] = [];
                foreach ($changes as $change) {
                    $locale = $this->localeRepository->findOneByIdentifier($change['locale']);
                    $canViewLocale = !$attribute->isLocalizable() || $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale);
                    if ($canViewLocale) {
                        $canReview =
                            $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute) &&
                            $this->authorizationChecker->isGranted(Attributes::OWN, $proposalProduct->getEntityWithValue()) &&
                            (!$attribute->isLocalizable() || $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale));
                        $present = $this->changesExtension->presentChange($proposalProduct, $change, $attributeCode);
                        if (count($present) > 0) {
                            $present['attributeLabel'] = $attribute->getLabel();
                            $present['scope'] = $change['scope'];
                            $present['locale'] = $change['locale'];
                            $present['localeLabel'] = $locale ? $locale->getName() : null;
                            $present['canReview'] = $canReview;
                            $proposalChanges[$attributeCode][] = $present;
                        }
                    }
                }
            }
        }

        return [
            'status' => 'ready',
            'status_label' => $this->statusExtension->getDraftStatusGrid($proposalProduct),
            'search_id' => $proposalProduct->getEntityWithValue()->getIdentifier(),
            'document_type' => 'product_dragft',
            'changes' => $proposalChanges,
            'author_code' => $proposalProduct->getAuthor(),
            'id' => $proposalProduct->getId(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductDraft && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
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
    private function getValueCollectionFromChangesWithoutEmptyValues(ProductDraft $proposal): WriteValueCollection
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

                $valueCollection->add($this->valueFactory->createByCheckingData(
                    $attribute,
                    $change['scope'],
                    $change['locale'],
                    $change['data']
                ));
            }
        }

        return $valueCollection;
    }

    private function getChanges(ProductDraft $proposal, array $context): array
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
                        'scope' => $change['scope']
                    ];
                }
            }
        }

        return $normalizedValues;
    }

    private function isChangeDataNull($changeData): bool
    {
        return null === $changeData || '' === $changeData || [] === $changeData;
    }

    private function changeNeedsReview(
        ProductDraft $proposal,
        string $code,
        ?string $localeCode,
        ?string $channelCode
    ): bool {
        return EntityWithValuesDraftInterface::CHANGE_TO_REVIEW === $proposal->getReviewStatusForChange($code, $localeCode, $channelCode);
    }
}
