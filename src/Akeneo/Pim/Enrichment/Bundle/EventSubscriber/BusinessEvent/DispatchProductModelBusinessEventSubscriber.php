<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchProductModelBusinessEventSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        Security $security,
        NormalizerInterface $normalizer,
        MessageBusInterface $messageBus
    ) {
        $this->security = $security;
        $this->normalizer = $normalizer;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['produceBusinessSaveEvent', 1000],
            StorageEvents::POST_REMOVE => ['produceBusinessRemoveEvent', 1000],
        ];
    }

    public function produceBusinessSaveEvent(GenericEvent $event): void
    {
        /** @var ProductModelInterface */
        $productModel = $event->getSubject();

        if (false === $productModel instanceof ProductModelInterface) {
            return;
        }

        /** @var UserInterface $user */
        $user = $this->security->getUser();

        if (!$user) {
            // TODO: https://akeneo.atlassian.net/browse/CXP-443
            // throw new \LogicException('User should not be null.');
            return;
        }

        $author = Author::fromUser($user);
        $data = $this->normalizer->normalize($productModel, 'standard');

        $message = null;

        if ($event->hasArgument('is_new') && true === $event->getArgument('is_new')) {
            $message = new ProductModelCreated($author, $data);
        } else {
            $message = new ProductModelUpdated($author, $data);
        }

        $this->messageBus->dispatch($message);
    }

    public function produceBusinessRemoveEvent(GenericEvent $event): void
    {
        /** @var ProductModelInterface */
        $productModel = $event->getSubject();
        if (false === $productModel instanceof ProductModelInterface) {
            return;
        }

        /** @var UserInterface $user */
        $user = $this->security->getUser();

        if (!$user) {
            // TODO: https://akeneo.atlassian.net/browse/CXP-443
            // throw new \LogicException('User should not be null.');
            return;
        }

        $author = Author::fromUser($user);
        $data = $this->normalizeProductModelData($productModel);

        $message = new ProductModelRemoved($author, $data);

        $this->messageBus->dispatch($message);
    }

    private function normalizeProductModelData(ProductModelInterface $productModel): array
    {
        return [
            'code' => $productModel->getCode(),
            'categories' => $productModel->getCategoryCodes(),
        ];
    }
}
