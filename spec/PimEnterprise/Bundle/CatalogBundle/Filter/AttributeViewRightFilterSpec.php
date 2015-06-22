<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AttributeViewRightFilterSpec extends ObjectBehavior
{
    public function let(SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($securityContext);
    }

    public function it_does_not_filter_an_attribute_if_the_user_is_granted_to_view_attribute_group($securityContext, AttributeInterface $price, AttributeGroupInterface $marketing)
    {
        $price->getGroup()->willReturn($marketing);
        $securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_an_attribute_if_the_user_is_not_granted_to_view_attribute_group($securityContext, AttributeInterface $price, AttributeGroupInterface $marketing)
    {
        $price->getGroup()->willReturn($marketing);
        $securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $price)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$price, 'pim:attribute:view', ['locales' => ['en_US']]]);
    }
}
