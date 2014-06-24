<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class PropositionWidgetSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $registry,
        EntityRepository $repository
    ) {
        $registry->getRepository('PimEnterprise\Bundle\WorkflowBundle\Model\Proposition')->willReturn($repository);

        $this->beConstructedWith($registry);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposition_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:propositions.html.twig');
    }

    function it_passes_propositions_from_the_repository_to_the_template($repository)
    {
        $repository
            ->findBy(
                [
                    'status' => Proposition::READY,
                ],
                ['createdAt' => 'desc'],
                10
            )
            ->willReturn(['proposition one', 'proposition two']);

        $this->getParameters()->shouldReturn(['params' => ['proposition one', 'proposition two']]);
    }
}
