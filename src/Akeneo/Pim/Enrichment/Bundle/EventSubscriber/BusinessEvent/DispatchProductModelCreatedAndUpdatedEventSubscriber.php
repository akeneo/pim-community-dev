<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchProductModelCreatedAndUpdatedEventSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private MessageBusInterface $messageBus;
    private int $maxBulkSize;

    /** @var array<ProductModelCreated|ProductModelUpdated> */
    private array $events = [];

    public function __construct(Security $security, MessageBusInterface $messageBus, int $maxBulkSize)
    {
        $this->security = $security;
        $this->messageBus = $messageBus;
        $this->maxBulkSize = $maxBulkSize;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'createAndDispatchProductModelEvents',
            StorageEvents::POST_SAVE_ALL => 'dispatchBufferedProductModelEvents',
        ];
    }

    public function createAndDispatchProductModelEvents(GenericEvent $postSaveEvent): void
    {
        /** @var ProductModelInterface */
        $productModel = $postSaveEvent->getSubject();
        if (false === $productModel instanceof ProductModelInterface) {
            return;
        }

        if (null === $user = $this->getUser()) {
            return;
        }

        $author = Author::fromUser($user);
        $data = [
            'code' => $productModel->getCode()
        ];

        $event = null;
        if ($postSaveEvent->hasArgument('is_new') && true === $postSaveEvent->getArgument('is_new')) {
            $event = new ProductModelCreated($author, $data);
        } else {
            $event = new ProductModelUpdated($author, $data);
        }

        if ($postSaveEvent->hasArgument('unitary') && true === $postSaveEvent->getArgument('unitary')) {
            $this->messageBus->dispatch(new BulkEvent([$event]));

            return;
        }

        $this->events[] = $event;

        if (count($this->events) >= $this->maxBulkSize) {
            $this->dispatchBufferedProductModelEvents();
        }
    }

    public function dispatchBufferedProductModelEvents(): void
    {
        if (count($this->events) > 0) {
            $this->messageBus->dispatch(new BulkEvent($this->events));
            $this->events = [];
        }
    }

    private function getUser(): ?UserInterface
    {
        $user = $this->security->getUser();
        // TODO: https://akeneo.atlassian.net/browse/CXP-443
        // if (null === $user) {
        //     throw new \LogicException('User should not be null.');
        // }

        return $user;
    }
}
