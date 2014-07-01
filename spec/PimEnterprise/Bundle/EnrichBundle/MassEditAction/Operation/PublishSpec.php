<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class PublishSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Publish');
    }

    function let(
        PublishedProductManager $manager,
        SecurityContextInterface $securityContext
    ) {
        $this->beConstructedWith($manager, $securityContext);
    }

    function it_gets_form_type_alias()
    {
        $this->getFormType()->shouldReturn('pimee_enrich_mass_publish');
    }

    function it_publishes_each_product(ProductInterface $product, SecurityContextInterface $securityContext, PublishedProductManager $manager)
    {
        $this->setObjectsToMassEdit([$product]);
        $securityContext->isGranted(Attributes::OWNER, $product)->willReturn(true);
        $manager->publish($product)->shouldBeCalled();
        $this->perform();
    }
}
