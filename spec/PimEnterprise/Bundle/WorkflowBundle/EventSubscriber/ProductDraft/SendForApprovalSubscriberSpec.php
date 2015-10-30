<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class SendForApprovalSubscriberSpec extends ObjectBehavior
{
    function let(
        NotificationManager $notificationManager,
        CategoryAccessRepository $categoryAccessRepo,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($notificationManager, $categoryAccessRepo, $userRepository);
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ]);
    }

    function it_sends_notification_to_owners_which_want_to_receive_them(
        $notificationManager,
        $categoryAccessRepo,
        $userRepository,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        UserInterface $owner1,
        UserInterface $owner2,
        UserInterface $owner3,
        UserInterface $author
    ) {
        $event->getSubject()->willReturn($productDraft);

        $product->getId()->willReturn(666);
        $product->getLabel()->willReturn('Light Saber');

        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');

        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');

        $owner1->hasProposalsToReviewNotification()->willReturn(true);
        $owner2->hasProposalsToReviewNotification()->willReturn(false);
        $owner3->hasProposalsToReviewNotification()->willReturn(true);

        $owners   = [$owner1, $owner2, $owner3];
        $groupIds = [
            [
                'id' => 2,
                'label' => 'Admin'
            ],
            [
                'id' => 4,
                'label' => 'Catalog Manager'
            ]
        ];

        $categoryAccessRepo->getGrantedUserGroupsForProduct($product, Attributes::OWN_PRODUCTS)->willReturn($groupIds);

        $userRepository->findByGroups([2, 4])->willReturn($owners);
        $userRepository->findOneBy(['username' => 'mary'])->willReturn($author);

        $notificationManager->notify(
            [$owner1, $owner3],
            'pimee_workflow.proposal.to_review',
            'success',
            [
                'route'         => 'pim_enrich_product_edit',
                'routeParams'   => [
                    'id'  => 666,
                    'redirectTab' => 'pim-product-edit-form-proposals'
                ],
                'messageParams' => [
                    '%product.label%'    => 'Light Saber',
                    '%author.firstname%' => 'Mary',
                    '%author.lastname%'  => 'Chobu'
                ],
                'context'       => [
                    'actionType'       => 'pimee_workflow_product_draft_notification_new_proposal',
                    'showReportButton' => false
                ]
            ]
        );
    }
}
