<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AttributeEditRightFilterSpec extends ObjectBehavior
{
    public function let(SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($securityContext);
    }

    public function it_does_not_filter_an_attribute_if_the_user_is_granted_to_edit_attribute_group(
        $securityContext,
        AttributeInterface $price,
        AttributeGroupInterface $marketing
    ) {
        $price->getGroup()->willReturn($marketing);
        $securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:edit', [])->shouldReturn(false);
    }

    public function it_filters_an_attribute_if_the_user_is_not_granted_to_edit_attribute_group(
        $securityContext,
        AttributeInterface $price,
        AttributeGroupInterface $marketing
    ) {
        $price->getGroup()->willReturn($marketing);
        $securityContext->isGranted(Attributes::EDIT_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:edit', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $price)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$price, 'pim:attribute:edit', ['locales' => ['en_US']]]);
    }
}
