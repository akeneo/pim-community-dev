<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PublishSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Publish');
    }

    function let(PublishedProductManager $manager, SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($manager, $securityContext, 'root/dir', 'prod');
    }

    function it_gets_form_type_alias()
    {
        $this->getFormType()->shouldReturn('pimee_enrich_mass_publish');
    }
}
