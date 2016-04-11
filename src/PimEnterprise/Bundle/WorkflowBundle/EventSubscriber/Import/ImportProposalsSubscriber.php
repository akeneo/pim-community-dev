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
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Provider\OwnerGroupsProvider;
use PimEnterprise\Bundle\WorkflowBundle\Provider\UsersToNotifyProvider;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
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

    const PROPOSAL_IMPORT_ALIAS = 'csv_product_proposal_import';

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
        $this->notifier            = $notifier;
        $this->userRepository      = $userRepository;
        $this->ownerGroupsProvider = $ownerGroupsProvider;
        $this->usersProvider       = $usersProvider;
        $this->jobRepository       = $jobRepository;
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
            && self::PROPOSAL_IMPORT_ALIAS === $event->getJobExecution()->getJobInstance()->getAlias()) {
            $author = $this->userRepository->findOneBy(['username' => $event->getJobExecution()->getUser()]);
            $usersToNotify = $this->usersProvider->getUsersToNotify($this->ownerGroupIds);

            if (!empty($usersToNotify)) {
                $jobCode = $event->getJobExecution()->getJobInstance()->getCode();
                $index = array_search($author, $usersToNotify, true);
                if (false !== $index) {
                    $this->sendProposalsNotification([$author], $jobCode);
                    unset($usersToNotify[$index]);
                }
                $this->sendProposalsNotification($usersToNotify, $jobCode, $author);
            }
            $this->ownerGroupIds = [];
        }
    }

    /**
     * Notify users that proposals are available for review. If author is set, add the name of the author in the
     * message.
     *
     * @param UserInterface[]    $users
     * @param string             $jobCode
     * @param UserInterface|null $author
     */
    protected function sendProposalsNotification(array $users, $jobCode, UserInterface $author = null)
    {
        $notification = $this->notificationFactory->create();
        $notification
            ->setType('add')
            ->setMessage('pimee_workflow.proposal.generic_import');

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $jobCode,
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

    /**
     * Check if the code of product draft import is a proposal import code.
     *
     * @param string $code The job instance code
     *
     * @return bool
     */
    protected function isProposalImport($code)
    {
        return null !== $this->jobRepository->findOneBy([
            'alias' => self::PROPOSAL_IMPORT_ALIAS,
            'code'  => $code
        ]);
    }
}
