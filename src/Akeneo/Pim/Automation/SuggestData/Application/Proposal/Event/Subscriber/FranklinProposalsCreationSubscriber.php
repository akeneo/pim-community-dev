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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Query\Subscription\EmptySuggestedDataQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class FranklinProposalsCreationSubscriber implements EventSubscriberInterface
{
    /** @var EmptySuggestedDataQueryInterface */
    private $emptySuggestedDataQuery;

    /**
     * @param EmptySuggestedDataQueryInterface $emptySuggestedDataQuery
     */
    public function __construct(EmptySuggestedDataQueryInterface $emptySuggestedDataQuery)
    {
        $this->emptySuggestedDataQuery = $emptySuggestedDataQuery;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED => 'emptySuggestedData',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function emptySuggestedData(GenericEvent $event): void
    {
        $suggestedData = $event->getSubject();
        if (!is_array($suggestedData)) {
            throw new \InvalidArgumentException('Event\'s subject must be an array');
        }
        foreach ($suggestedData as $data) {
            if (!$data instanceof SuggestedData) {
                throw new \InvalidArgumentException(
                    sprintf('Event\'s subject must be an array of %s', SuggestedData::class)
                );
            }
        }

        $subscriptionIds = array_map(
            function (SuggestedData $data) {
                return $data->getSubscriptionId();
            },
            $suggestedData
        );

        $this->emptySuggestedDataQuery->execute($subscriptionIds);
    }
}
