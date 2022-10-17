<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\ProductDraft\UpdateDraftAuthor;
use Akeneo\UserManagement\Component\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Modify the author in the product drafts if the username has been modified.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UpdateDraftAuthorSubscriber implements EventSubscriberInterface
{
    /** @var UpdateDraftAuthor */
    private $updateProductDraftUsername;

    /**
     * @param UpdateDraftAuthor $updateProductDraftUsername
     */
    public function __construct(UpdateDraftAuthor $updateProductDraftUsername)
    {
        $this->updateProductDraftUsername = $updateProductDraftUsername;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [UserEvent::POST_UPDATE => 'updateDraftAuthor'];
    }

    /**
     * @param GenericEvent $event
     */
    public function updateDraftAuthor(GenericEvent $event): void
    {
        $user = $event->getSubject();

        if (!is_object($user) || !$user instanceof UserInterface) {
            return;
        }

        if (!$this->isUsernameUpdated($event)) {
            return;
        }

        $this->updateProductDraftUsername->execute($event->getArgument('previous_username'), $user->getUserIdentifier());
    }

    /**
     * @param GenericEvent $event
     *
     * @return bool
     */
    private function isUsernameUpdated(GenericEvent $event): bool
    {
        $user = $event->getSubject();

        return $event->getArgument('previous_username') !== null
            && $user->getUserIdentifier() !== $event->getArgument('previous_username');
    }
}
