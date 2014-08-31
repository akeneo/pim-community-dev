<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;

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
    }

    function it_passes_product_drafts_from_the_repository_to_the_template($accessRepo, $user, $ownershipRepo)
    {
        $accessRepo->isOwner($user)->willReturn(true);
        $ownershipRepo->findApprovableByUser($user, 10)->willReturn(['product draft one', 'product draft two']);

        $this->getParameters()->shouldReturn(['show' => true, 'params' => ['product draft one', 'product draft two']]);
    }
}
