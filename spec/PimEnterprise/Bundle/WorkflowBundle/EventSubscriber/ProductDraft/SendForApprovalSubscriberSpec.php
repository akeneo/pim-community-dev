<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
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
        UserRepositoryInterface $userRepo
    ) {
        $this->beConstructedWith($notificationManager, $categoryAccessRepo, $userRepo);
    }

    function it_subscribes_to_approve_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ]);
    }

    function it_sends_notification_to_owners(
        $notificationManager,
        $categoryAccessRepo,
        $userRepo,
        GenericEvent $event,
        ProductDraftInterface $productDraft,
        ProductInterface $product,
        UserInterface $owner1,
        UserInterface $owner2,
        UserInterface $author
    ) {
        $event->getSubject()->willReturn($productDraft);
        $product->getId()->willReturn(666);
        $product->getLabel()->willReturn('Light Saber');
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('mary');
        $author->getFirstName()->willReturn('Mary');
        $author->getLastName()->willReturn('Chobu');

        $owners = [$owner1, $owner2];
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
        $userRepo->findByGroups([2, 4])->willReturn($owners);
        $userRepo->findOneBy(['username' => 'mary'])->willReturn($author);

        $notificationManager->notify(
            $owners,
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
