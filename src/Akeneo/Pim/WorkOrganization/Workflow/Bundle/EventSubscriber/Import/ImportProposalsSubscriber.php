<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Import;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\OwnerGroupsProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Provider\UsersToNotifyProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class ImportProposalsSubscriber
 *
 * This subscriber listens events during import process, to send notifications for users at the end of import.
 * This class is stateful because we keep group ids in memory during the import process, and is empty only when
 * notifications were send.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ImportProposalsSubscriber implements EventSubscriberInterface
{
    const NOTIFICATION_TYPE = 'pimee_workflow_import_notification_new_proposals';

    private const PRODUCT_PROPOSAL_IMPORT_ALIAS = 'csv_product_proposal_import';
    private const PRODUCT_MODEL_PROPOSAL_IMPORT_ALIAS = 'csv_product_model_proposal_import';

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var OwnerGroupsProvider */
    protected $ownerGroupsProvider;

    /** @var UsersToNotifyProvider */
    protected $usersProvider;

    /** @var ObjectRepository */
    protected $jobRepository;

    /** @var SimpleFactoryInterface */
    protected $notificationFactory;

    /** @var array */
    protected $ownerGroupIds = [];

    /** @var string */
    protected $username;

    /** @var ?UserInterface */
    private $author;

    /**
     * @param NotifierInterface       $notifier
     * @param UserRepositoryInterface $userRepository
     * @param OwnerGroupsProvider     $ownerGroupsProvider
     * @param UsersToNotifyProvider   $usersProvider
     * @param ObjectRepository        $jobRepository
     * @param SimpleFactoryInterface  $notificationFactory
     */
    public function __construct(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        ObjectRepository $jobRepository,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider = $usersProvider;
        $this->jobRepository = $jobRepository;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE            => 'saveGroupIdsToNotify',
            EventInterface::AFTER_JOB_EXECUTION => 'notifyUsers',
        ];
    }

    /**
     * Adds the group ids owner of a product to an instance variable. This function only works on ProductDraft saves
     * from proposal imports.
     *
     * @param GenericEvent $event
     */
    public function saveGroupIdsToNotify(GenericEvent $event)
    {
        $draft = $event->getSubject();
        if ($draft instanceof EntityWithValuesDraftInterface) {
            $entityWithValues = $draft->getEntityWithValue();
            $ownerGroupIds = $this->ownerGroupsProvider->getOwnerGroupIds($entityWithValues);

            $this->ownerGroupIds = array_unique(array_merge($this->ownerGroupIds, $ownerGroupIds));
            $this->username = $draft->getAuthor();
        }
    }

    /**
     * Notify the users at the end of an import. Can send 2 types of notifications: one for the author of the import
     * and one for each owner of a product.
     *
     * @param JobExecutionEvent $event
     */
    public function notifyUsers(JobExecutionEvent $event)
    {
        $draftJobs = [self::PRODUCT_PROPOSAL_IMPORT_ALIAS, self::PRODUCT_MODEL_PROPOSAL_IMPORT_ALIAS];

        if (!empty($this->ownerGroupIds) && null !== $this->username
            && in_array($event->getJobExecution()->getJobInstance()->getJobName(), $draftJobs)) {
            $author = $this->userRepository->findOneBy(['username' => $this->username]);
            $usersToNotify = $this->usersProvider->getUsersToNotify($this->ownerGroupIds);

            if (!empty($usersToNotify)) {
                $index = array_search($author, $usersToNotify, true);
                if (false !== $index) {
                    $this->sendProposalsNotification([$author], $this->username);
                    unset($usersToNotify[$index]);
                }
                $this->sendProposalsNotification($usersToNotify, $this->username, $author);
            }

            $this->ownerGroupIds = [];
            $this->author = null;
        }
    }

    /**
     * Notify users that proposals are available for review. If author is set, add the name of the author in the
     * message.
     */
    protected function sendProposalsNotification(array $users, string $username, UserInterface $author = null)
    {
        $notification = $this->notificationFactory->create();
        $notification
            ->setType('add')
            ->setMessage('pimee_workflow.proposal.generic_import');

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $username,
                    ]
                ]
            ],
        ];

        $notification
            ->setRoute('pimee_workflow_proposal_index')
            ->setContext(
                [
                    'actionType'       => self::NOTIFICATION_TYPE,
                    'showReportButton' => false,
                    'gridParameters'   => http_build_query($gridParameters, 'flags_')
                ]
            );

        if (null !== $author) {
            $notification
                ->setMessage('pimee_workflow.proposal.individual_import')
                ->setMessageParams(
                    [
                        '%author.firstname%' => $author->getFirstName(),
                        '%author.lastname%'  => $author->getLastName()
                    ]
                );
        }

        $this->notifier->notify($notification, $users);
    }
}
