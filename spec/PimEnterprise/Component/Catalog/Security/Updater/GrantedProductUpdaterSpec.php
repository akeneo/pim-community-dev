<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class GrantedProductUpdaterSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productFieldFilter,
        FilterInterface $productAssociationFilter,
        FilterInterface $productFilter
    ) {
        $this->beConstructedWith(
            $productUpdater,
            $authorizationChecker,
            $productFieldFilter,
            $productAssociationFilter,
            $productFilter,
            ['categories', 'groups', 'enabled'],
            ['associations']
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Catalog\Security\Updater\GrantedProductUpdater');
    }

    function it_filters_fields_on_a_draft(
        $productUpdater,
        $authorizationChecker,
        $productFieldFilter,
        $productAssociationFilter,
        ProductInterface $product
    ) {
        $data = [
            'enabled' => true,
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ];
        $product->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);
        $productFieldFilter->filter($product, ['enabled' => true])->willReturn([]);
        $productAssociationFilter->filter($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ])->willReturn([]);

        $productUpdater->update($product, $data, [])->shouldBeCalled();
        $this->update($product, $data, []);
    }

    function it_filters_fields_on_a_viewable_product(
        $productUpdater,
        $authorizationChecker,
        $productFilter,
        $productAssociationFilter,
        ProductInterface $product
    ) {
        $data = [
            'enabled' => true,
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ];
        $product->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);
        $productFilter->filter($product, ['enabled' => true])->willReturn([]);
        $productAssociationFilter->filter($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ])->willReturn([]);

        $productUpdater->update($product, $data, [])->shouldBeCalled();
        $this->update($product, $data, []);
    }

    function it_throws_an_exception_if_user_tries_to_update_fields_on_a_draft(
        $productUpdater,
        $authorizationChecker,
        $productFieldFilter,
        $productAssociationFilter,
        ProductInterface $product
    ) {
        $data = [
            'enabled' => false,
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ];
        $product->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);
        $productFieldFilter->filter($product, ['enabled' => false])->willReturn(['enabled' => false]);
        $productAssociationFilter->filter($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ])->willReturn([]);

        $productUpdater->update($product, $data, [])->shouldNotBeCalled();

        $this->shouldThrow(InvalidArgumentException::class)->during('update', [$product, $data, []]);
    }

    function it_throws_an_exception_if_user_tries_to_update_fields_on_a_viewable_product(
        $productUpdater,
        $authorizationChecker,
        $productFilter,
        $productAssociationFilter,
        ProductInterface $product
    ) {
        $data = [
            'enabled' => false,
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ];
        $product->getId()->willReturn(1);
        $product->getIdentifier()->willReturn('product');

        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);
        $productFilter->filter($product, ['enabled' => false])->willReturn(['enabled' => false]);
        $productAssociationFilter->filter($product, [
            'associations' => [
                'X_SELL' => [
                    'products' => ['product_a', 'product_b']
                ]
            ]
        ])->willReturn([]);

        $productUpdater->update($product, $data, [])->shouldNotBeCalled();

        $this->shouldThrow(ResourceAccessDeniedException::class)->during('update', [$product, $data, []]);
    }

    function it_does_not_try_to_filter_fields_if_user_is_owner(
        $productUpdater,
        $authorizationChecker,
        $productFieldFilter,
        $productAssociationFilter,
        ProductInterface $product
    ) {
        $data = ['enabled' => true];
        $product->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $product)->willReturn(true);
        $productFieldFilter->filter($product, $data)->shouldNotBeCalled();
        $productAssociationFilter->filter($product, [])->shouldNotBeCalled();

        $productUpdater->update($product, $data, [])->shouldBeCalled();
        $this->update($product, $data, []);
    }

    function it_does_not_try_to_check_if_user_is_owner_if_product_is_new(
        $productUpdater,
        $authorizationChecker,
        ProductInterface $product
    ) {
        $data = ['enabled' => true];
        $product->getId()->willReturn(null);

        $authorizationChecker->isGranted([Attributes::OWN], $product)->shouldNotBeCalled();
        $authorizationChecker->isGranted([Attributes::EDIT], $product)->shouldNotBeCalled();

        $productUpdater->update($product, $data, [])->shouldBeCalled();
        $this->update($product, $data, []);
    }
}
