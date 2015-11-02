<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductValueLocaleRightFilterSpec extends ObjectBehavior
{
    public function let(
        AuthorizationCheckerInterface $authorizationChecker,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($authorizationChecker, $localeRepository);
    }

    public function it_does_not_filter_a_product_value_if_the_user_is_granted_to_see_its_locale(
        $authorizationChecker,
        $localeRepository,
        ProductValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS
    ) {
        $price->getAttribute()->willReturn($priceAttribute);
        $price->getLocale()->willReturn('en_US');
        $priceAttribute->isLocalizable()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(true);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_filters_a_product_value_if_the_user_is_not_granted_to_see_its_locale(
        $authorizationChecker,
        $localeRepository,
        ProductValueInterface $price,
        AttributeInterface $priceAttribute,
        LocaleInterface $enUS
    ) {
        $price->getAttribute()->willReturn($priceAttribute);
        $price->getLocale()->willReturn('en_US');
        $priceAttribute->isLocalizable()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $enUS)->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(true);
    }

    public function it_does_not_filter_a_product_value_if_the_attribute_is_not_localizable(
        ProductValueInterface $price,
        AttributeInterface $priceAttribute
    ) {
        $price->getAttribute()->willReturn($priceAttribute);
        $priceAttribute->isLocalizable()->willReturn(false);

        $this->filterObject($price, 'pim:product_value:view', [])->shouldReturn(false);
    }

    public function it_fails_if_it_is_not_a_product_value(\StdClass $anOtherObject)
    {
        $this->shouldThrow('\LogicException')->during(
            'filterObject',
            [$anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]]
        );
    }
}
