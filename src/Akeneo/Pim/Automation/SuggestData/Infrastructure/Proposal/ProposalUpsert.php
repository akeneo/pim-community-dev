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

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
final class ProposalUpsert implements ProposalUpsertInterface
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
     * @param ObjectUpdaterInterface                $productUpdater
     * @param EntityWithValuesDraftBuilderInterface $draftBuilder
     * @param SaverInterface                        $draftSaver
     * @param EventDispatcherInterface              $eventDispatcher
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
    public function process(ProductInterface $product, array $values, string $author): void
    {
        $this->productUpdater->update(
            $product,
            [
                'values' => $values,
            ]
        );
        $productDraft = $this->draftBuilder->build($product, $author);

        if (null !== $productDraft) {
            $this->eventDispatcher->dispatch(
                EntityWithValuesDraftEvents::PRE_READY,
                new GenericEvent($productDraft)
            );

            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productDraft->markAsReady();
            $this->draftSaver->save($productDraft);

            $this->eventDispatcher->dispatch(
                EntityWithValuesDraftEvents::POST_READY,
                new GenericEvent($productDraft, ['comment' => null])
            );
        }
    }
}
