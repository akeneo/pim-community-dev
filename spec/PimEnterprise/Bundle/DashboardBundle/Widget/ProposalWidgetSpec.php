<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

class ProposalWidgetSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, EntityRepository $repository)
    {
        $registry->getRepository('PimEnterprise\Bundle\CatalogBundle\Model\Proposal')->willReturn($repository);
        $repository->findBy(Argument::cetera())->willReturn([]);

        $this->beConstructedWith($registry);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposal_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:proposals.html.twig');
    }

    function it_exposes_the_proposal_widget_template_parameters()
    {
        $this->getParameters()->shouldReturn(['params' => []]);
    }

    function it_passes_proposals_from_the_repository_to_the_template($repository)
    {
        $repository->findBy([], ['createdAt' => 'DESC'], 10)->willReturn(['proposal one', 'proposal two']);
        $this->getParameters()->shouldReturn(['params' => ['proposal one', 'proposal two']]);
    }
}
