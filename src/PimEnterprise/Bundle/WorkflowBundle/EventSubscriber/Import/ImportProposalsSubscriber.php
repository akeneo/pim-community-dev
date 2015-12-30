<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Import;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
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

    const PROPOSAL_IMPORT_CODE = 'csv_product_proposal_import';

    /** @var NotificationManager */
    protected $notificationManager;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var OwnerGroupsProvider */
    protected $ownerGroupsProvider;

    /** @var UsersToNotifyProvider */
    protected $usersProvider;

    /** @var ObjectRepository */
    protected $jobRepository;

    /** @var array */
    protected $ownerGroupIds = [];

    /**
     * @param NotificationManager     $notificationManager
     * @param UserRepositoryInterface $userRepository
     * @param OwnerGroupsProvider     $ownerGroupsProvider
     * @param UsersToNotifyProvider   $usersProvider
     * @param ObjectRepository        $jobRepository
     */
    public function __construct(
        NotificationManager $notificationManager,
        UserRepositoryInterface $userRepository,
        OwnerGroupsProvider $ownerGroupsProvider,
        UsersToNotifyProvider $usersProvider,
        ObjectRepository $jobRepository
    ) {
        $this->notificationManager = $notificationManager;
        $this->userRepository      = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider       = $usersProvider;
        $this->jobRepository       = $jobRepository;
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
     * Adds the group ids owner of a product to an instance variable. This function works only on ProductDraft saves
     * and only in import events.
     *
     * @param GenericEvent $event
     */
    public function saveGroupIdsToNotify(GenericEvent $event)
    {
        $productDraft = $event->getSubject();
        if ($productDraft instanceof ProductDraftInterface && $this->isProposalImport($productDraft->getAuthor())) {
            $product = $productDraft->getProduct();
            $ownerGroupIds = $this->ownerGroupsProvider->getOwnerGroupIds($product);

            $this->ownerGroupIds = array_unique(array_merge($this->ownerGroupIds, $ownerGroupIds));
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
        if (!empty($this->ownerGroupIds)
            && self::PROPOSAL_IMPORT_CODE === $event->getJobExecution()->getJobInstance()->getAlias()) {
            $author = $this->userRepository->findOneBy(['username' => $event->getJobExecution()->getUser()]);
            $usersToNotify = $this->usersProvider->getUsersToNotify($this->ownerGroupIds);

            if (!empty($usersToNotify)) {
                $index = array_search($author, $usersToNotify, true);
                if (false !== $index) {
                    $this->sendProposalsNotification([$author]);
                    unset($usersToNotify[$index]);
                }
                $this->sendProposalsNotification($usersToNotify, $author);
            }
            $this->ownerGroupIds = [];
        }
    }

    /**
     * Notify users that proposals are available for review. If author is set, add the name of the author in the
     * message.
     *
     * @param UserInterface[]    $users
     * @param UserInterface|null $author
     */
    protected function sendProposalsNotification(array $users, UserInterface $author = null)
    {
        $message = 'pimee_workflow.proposal.generic_import';
        $params = [
            'route'   => 'pimee_workflow_proposal_index',
            'context' => [
                'actionType'       => static::NOTIFICATION_TYPE,
                'showReportButton' => false
            ]
        ];

        if (null !== $author) {
            $message = 'pimee_workflow.proposal.individual_import';
            $params['messageParams'] = [
                '%author.firstname%' => $author->getFirstName(),
                '%author.lastname%'  => $author->getLastName()
            ];
        }

        $this->notificationManager->notify($users, $message, 'add', $params);
    }

    /**
     * Check if the code of product draft import is a proposal import code.
     *
     * @param $code The job instance code
     *
     * @return bool
     */
    protected function isProposalImport($code)
    {
        return null !== $this->jobRepository->findOneBy([
            'alias' => self::PROPOSAL_IMPORT_CODE,
            'code' => $code
        ]);
    }
}
