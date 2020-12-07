<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductRemovedEventDataBuilder as BaseProductRemovedEventDataBuilder;
use Akeneo\Pim\Enrichment\Product\Component\Product\Query\GetViewableCategoryCodes;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductRemovedEventDataBuilder implements EventDataBuilderInterface
{
    private BaseProductRemovedEventDataBuilder $baseProductRemovedEventDateBuilder;
    private GetViewableCategoryCodes $getViewableCategoryCodes;

    public function __construct(
        BaseProductRemovedEventDataBuilder $baseProductRemovedEventDateBuilder,
        GetViewableCategoryCodes $getViewableCategoryCodes
    ) {
        $this->baseProductRemovedEventDateBuilder = $baseProductRemovedEventDateBuilder;
        $this->getViewableCategoryCodes = $getViewableCategoryCodes;
    }

    public function supports(object $event): bool
    {
        return $this->baseProductRemovedEventDateBuilder->supports($event);
    }

    /**
     * @param BulkEvent $event
     */
    public function build(object $event, UserInterface $user): EventDataCollection
    {
        if (false === $this->supports($event)) {
            throw new \InvalidArgumentException();
        }

        $collection = new EventDataCollection();

        /** @var ProductRemoved $productRemovedEvent */
        foreach ($event->getEvents() as $productRemovedEvent) {
            $grantedCategoryCodes = $this->getViewableCategoryCodes->forCategoryCodes(
                $user->getId(),
                $productRemovedEvent->getCategoryCodes()
            );

            if (0 === count($grantedCategoryCodes)) {
                $collection->setEventDataError(
                    $productRemovedEvent,
                    new NotGrantedProductException($user->getUsername(), $productRemovedEvent->getIdentifier())
                );

                continue;
            }

            $data = [
                'resource' => [
                    'identifier' => $productRemovedEvent->getIdentifier(),
                ],
            ];

            $collection->setEventData($productRemovedEvent, $data);
        }

        return $collection;
    }
}
