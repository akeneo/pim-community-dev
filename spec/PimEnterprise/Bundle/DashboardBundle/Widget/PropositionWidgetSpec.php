<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;

class PropositionWidgetSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        EntityRepository $repository,
        CategoryOwnershipRepository $ownershipRepository,
        UserContext $context,
        User $user
    ) {
        $registry
            ->getRepository('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition')
            ->willReturn($repository);
        $registry
            ->getRepository('PimEnterprise\Bundle\SecurityBundle\Entity\CategoryOwnership')
            ->willReturn($ownershipRepository);
        $repository->findBy(Argument::cetera())->willReturn([]);

        $context->getUser()->willReturn($user);

        $this->beConstructedWith($registry, $context);
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

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($ownershipRepository, $user)
    {
        $ownershipRepository->isOwner($user)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
    }

    function it_passes_propositions_from_the_repository_to_the_template($ownershipRepository, $user, $repository)
    {
        $ownershipRepository->isOwner($user)->willReturn(true);
        $repository
            ->findBy(
                ['status' => Proposition::READY],
                ['createdAt' => 'desc'],
                10
            )
            ->willReturn(['proposition one', 'proposition two']);

        $this->getParameters()->shouldReturn(['show' => true, 'params' => ['proposition one', 'proposition two']]);
    }
}
