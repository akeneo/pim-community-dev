<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;

class ProductDraftWidgetSpec extends ObjectBehavior
{
    function let(
        ProductDraftOwnershipRepositoryInterface $ownershipRepository,
        CategoryAccessRepository $accessRepository,
        UserContext $context,
        User $user
    ) {
        $context->getUser()->willReturn($user);

        $this->beConstructedWith($accessRepository, $ownershipRepository, $context);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposition_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:propositions.html.twig');
    }

    function it_exposes_the_proposition_widget_template_parameters()
    {
        $this->getParameters()->shouldBeArray();
    }

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($accessRepository, $user)
    {
        $accessRepository->isOwner($user)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
    }

    function it_passes_propositions_from_the_repository_to_the_template($accessRepository, $user, $ownershipRepository)
    {
        $accessRepository->isOwner($user)->willReturn(true);
        $ownershipRepository->findApprovableByUser($user, 10)->willReturn(['proposition one', 'proposition two']);

        $this->getParameters()->shouldReturn(['show' => true, 'params' => ['proposition one', 'proposition two']]);
    }
}
