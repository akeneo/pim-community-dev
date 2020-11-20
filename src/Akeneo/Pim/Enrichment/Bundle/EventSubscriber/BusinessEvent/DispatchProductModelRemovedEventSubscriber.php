<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Component\EventQueue\Author;
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
final class DispatchProductModelRemovedEventSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private MessageBusInterface $messageBus;

    public function __construct(Security $security, MessageBusInterface $messageBus)
    {
        $this->security = $security;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'createAndDispatchProductModelEvents',
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
            'code' => $productModel->getCode(),
            'category_codes' => $productModel->getCategoryCodes(),
        ];

        $event = new ProductModelRemoved($author, $data);

        $this->messageBus->dispatch($event);
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
