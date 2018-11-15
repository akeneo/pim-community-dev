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

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsert implements ProposalUpsertInterface
{
    /** @var array */
    private $drafts = [];

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param ObjectUpdaterInterface $productUpdater
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ObjectUpdaterInterface $productUpdater, EventDispatcherInterface $eventDispatcher)
    {
        $this->productUpdater = $productUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $suggestedData, string $author): void
    {
        $processed = [];
        foreach ($suggestedData as $data) {
            $product = $data->getProduct();
            $this->productUpdater->update($product, ['values' => $data->getSuggestedValues()]);

            $key = sprintf('%s-%s', $product->getIdentifier(), $author);
            $this->drafts[$key] = $product->getValues()->toArray();
            $processed[] = $data->getProduct()->getId();
        }
        $this->eventDispatcher->dispatch(
            SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED,
            new GenericEvent($processed)
        );
    }

    /**
     * @param $identifier
     * @param $author
     *
     * @return bool
     */
    public function hasProposalForProduct($identifier, $author): bool
    {
        return array_key_exists(sprintf('%s-%s', $identifier, $author), $this->drafts);
    }
}
