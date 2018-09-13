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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\CreateProposalInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class CreateProposal implements CreateProposalInterface
{
    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $draftBuilder;

    /** @var SaverInterface */
    private $draftSaver;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param ObjectUpdaterInterface $productUpdater
     * @param EntityWithValuesDraftBuilderInterface $draftBuilder
     * @param SaverInterface $draftSaver
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productUpdater = $productUpdater;
        $this->draftBuilder = $draftBuilder;
        $this->draftSaver = $draftSaver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function fromSuggestedData(EntityWithValuesInterface $product, array $suggestedData, string $author): void
    {
        try {
            $this->productUpdater->update(
                $product,
                [
                    'values' => $suggestedData,
                ]
            );
            $productDraft = $this->draftBuilder->build($product, $author);
        } catch (PropertyException $e) {
            // TODO APAI-244: handle error
            return;
        }

        if (null !== $productDraft) {
            $this->eventDispatcher->dispatch(
                EntityWithValuesDraftEvents::PRE_READY,
                new GenericEvent($productDraft)
            );

            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $this->draftSaver->save($productDraft);

            // TODO APAI-252: handle notifications
            //$this->eventDispatcher->dispatch(EntityWithValuesDraftEvents::POST_READY, new GenericEvent($productDraft));
        }
    }
}
