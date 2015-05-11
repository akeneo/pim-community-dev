<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductValueAttributeGroupRightFilterSpec extends ObjectBehavior
{
    public function let(SecurityContextInterface $securityContext)
    {
        $this->beConstructedWith($securityContext);
    }

    public function it_does_not_filter_a_product_value_if_the_user_is_granted_to_see_its_attribute_group($securityContext, ProductValueInterface $price, AttributeInterface $priceAttribute, AttributeGroupInterface $marketing)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->getGroup()->willReturn($marketing);

        $securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_the_user_is_not_granted_to_see_its_attribute_group($securityContext, ProductValueInterface $price, AttributeInterface $priceAttribute, AttributeGroupInterface $marketing)
    {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->getGroup()->willReturn($marketing);

        $securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $marketing)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during('filterObject', [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]);
    }
}
