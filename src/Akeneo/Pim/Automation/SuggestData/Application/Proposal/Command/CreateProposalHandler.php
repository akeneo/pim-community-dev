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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandler
{
    /** @var string */
    private const PROPOSAL_AUTHOR = 'Franklin Insights';

    /** @var SuggestedDataNormalizer */
    private $suggestedDataNormalizer;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $draftBuilder;

    /** @var SaverInterface */
    private $productDraftSaver;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param SuggestedDataNormalizer $suggestedDataNormalizer
     * @param ObjectUpdaterInterface $productUpdater
     * @param EntityWithValuesDraftBuilderInterface $draftBuilder
     * @param SaverInterface $productDraftSaver
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $productDraftSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->suggestedDataNormalizer = $suggestedDataNormalizer;
        $this->productUpdater = $productUpdater;
        $this->draftBuilder = $draftBuilder;
        $this->productDraftSaver = $productDraftSaver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CreateProposalCommand $command
     */
    public function handle(CreateProposalCommand $command): void
    {
        $product = $command->getProductSubscription()->getProduct();
        if (0 === count($product->getCategoryCodes())) {
            // TODO APAI-244: handle error
            return;
        }

        $suggestedValues = $this->getSuggestedValues(
            $command->getProductSubscription()->getSuggestedData(),
            $product->getFamily()
        );

        if (empty($suggestedValues)) {
            // TODO APAI-244: handle error?
            return;
        }

        $this->createProposal($product, $suggestedValues);

        // TODO APAI-240: empty suggested data from subscription
    }

    /**
     * @param SuggestedData $suggestedData
     * @param FamilyInterface $family
     *
     * @return array
     */
    private function getSuggestedValues(SuggestedData $suggestedData, FamilyInterface $family): array
    {
        $normalizedData = $this->suggestedDataNormalizer->normalize($suggestedData);
        $availableAttributes = $family->getAttributeCodes();

        return array_filter(
            $normalizedData,
            function ($attributeCode) use ($availableAttributes) {
                return in_array($attributeCode, $availableAttributes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Creates a draft and submits it for approval
     *
     * @param ProductInterface $product
     * @param array $suggestedValues
     */
    private function createProposal(ProductInterface $product, array $suggestedValues): void
    {
        try {
            $this->productUpdater->update(
                $product,
                [
                    'values' => $suggestedValues,
                ]
            );
            $productDraft = $this->draftBuilder->build($product, self::PROPOSAL_AUTHOR);

            if (null !== $productDraft) {
                $this->eventDispatcher->dispatch(
                    EntityWithValuesDraftEvents::PRE_READY,
                    new GenericEvent($productDraft)
                );

                $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
                $this->productDraftSaver->save($productDraft);

                // TODO APAI-252: handle notifications
                //$this->eventDispatcher->dispatch(EntityWithValuesDraftEvents::POST_READY, new GenericEvent($productDraft));
            }
        } catch (\Exception $e) {
            // TODO APAI-244: handle error
            return;
        }
    }
}
