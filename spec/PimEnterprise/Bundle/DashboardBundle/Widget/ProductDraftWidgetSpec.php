<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ProductDraftWidgetSpec extends ObjectBehavior
{
    function let(
        ProductDraftOwnershipRepositoryInterface $ownershipRepo,
        CategoryAccessRepository $accessRepo,
        UserContext $context,
        User $user
    ) {
        $context->getUser()->willReturn($user);

        $this->beConstructedWith($accessRepo, $ownershipRepo, $context);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_product_draft_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:product_drafts.html.twig');
    }

    function it_exposes_the_product_draft_widget_template_parameters()
    {
        $this->getParameters()->shouldBeArray();
    }

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($accessRepo, $user)
    {
        $accessRepo->isOwner($user)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
        $this->getData()->shouldReturn([]);
    }

    function it_exposes_product_drafts_data(
        $accessRepo,
        $user,
        $ownershipRepo,
        ProductDraft $first,
        ProductDraft $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct
    ) {
        $accessRepo->isOwner($user)->willReturn(true);
        $ownershipRepo->findApprovableByUser($user, 10)->willReturn([$first, $second]);

        $first->getProduct()->willReturn($firstProduct);
        $second->getProduct()->willReturn($secondProduct);

        $firstProduct->getId()->willReturn(1);
        $secondProduct->getId()->willReturn(2);
        $firstProduct->getLabel()->willReturn('First product');
        $secondProduct->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('Julia');
        $second->getAuthor()->willReturn('Julia');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $this->getData()->shouldReturn(
            [
                [
                    'productId'    => 1,
                    'productLabel' => 'First product',
                    'author'       => 'Julia',
                    'createdAt'    => $firstCreatedAt->format('U')
                ],
                [
                    'productId'    => 2,
                    'productLabel' => 'Second product',
                    'author'       => 'Julia',
                    'createdAt'    => $secondCreatedAt->format('U')
                ]
            ]
        );
    }
}
