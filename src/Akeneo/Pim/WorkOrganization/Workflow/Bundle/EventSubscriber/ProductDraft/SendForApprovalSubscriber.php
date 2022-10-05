<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\OwnerGroupsProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\UsersToNotifyProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber listens to entity with values draft submission for approval.
 * This way, we can send notifications to the right users.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class SendForApprovalSubscriber implements EventSubscriberInterface
{
    const NOTIFICATION_TYPE = 'pimee_workflow_product_draft_notification_new_proposal';

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var OwnerGroupsProvider */
    protected $ownerGroupsProvider;

    /** @var UsersToNotifyProvider */
    protected $usersProvider;

    /** @var SimpleFactoryInterface */
    protected $notificationFactory;

    public function __construct(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider = $usersProvider;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityWithValuesDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ];
    }

    public function sendNotificationToOwners(GenericEvent $event): void
    {
        $entityWithValuesDraft = $event->getSubject();
        $entityWithValue = $entityWithValuesDraft->getEntityWithValue();

        $groupsToNotify = $this->ownerGroupsProvider->getOwnerGroupIds($entityWithValue);
        if (empty($groupsToNotify)) {
            return;
        }

        $filters = ['locales' => $this->getChangeToReviewLocales($event->getSubject())];
        $usersToNotify = $this->usersProvider->getUsersToNotify($groupsToNotify, $filters);
        if (empty($usersToNotify)) {
            return;
        }

        $author = $this->userRepository->findOneBy(['username' => $entityWithValuesDraft->getAuthor()]);
        $authorCatalogLocale = $author->getCatalogLocale()->getCode();

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $author->getUsername(),
                    ],
                ],
                'identifier'    => [
                    'value' => $entityWithValue instanceof ProductInterface ? $entityWithValue->getIdentifier() : $entityWithValue->getCode(),
                    'type' => 1,
                ],
            ],
        ];

        $notification = $this->notificationFactory->create();
        $notification
            ->setMessage('pimee_workflow.proposal.to_review')
            ->setMessageParams(
                [
                    '%product.label%'    => $entityWithValue->getLabel($authorCatalogLocale),
                    '%author.firstname%' => $author->getFirstName(),
                    '%author.lastname%'  => $author->getLastName()
                ]
            )
            ->setType('add')
            ->setRoute('pimee_workflow_proposal_index')
            ->setComment($event->getArgument('comment'))
            ->setContext(
                [
                    'actionType'       => static::NOTIFICATION_TYPE,
                    'showReportButton' => false,
                    'gridParameters'   => http_build_query($gridParameters, 'flags_')
                ]
            );

        $this->notifier->notify($notification, $usersToNotify);
    }

    /**
     * Return the locales on which there is some changes to review.
     * If a change is not localized, returns null.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @return string[]
     */
    private function getChangeToReviewLocales(EntityWithValuesDraftInterface $entityWithValuesDraft): ?array
    {
        $changes = $entityWithValuesDraft->getChangesToReview();

        if (!isset($changes['values'])) {
            return null;
        }

        $locales = [];
        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                if ($change['locale'] === null) {
                    return null;
                }

                $locales[$change['locale']] = 1;
            }
        }

        return array_keys($locales);
    }
}
