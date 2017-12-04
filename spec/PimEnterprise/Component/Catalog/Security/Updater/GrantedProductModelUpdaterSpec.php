<?php

namespace spec\PimEnterprise\Component\Catalog\Security\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Catalog\Security\Updater\GrantedProductModelUpdater;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class GrantedProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productModelUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productModelFilter
    ) {
        $this->beConstructedWith(
            $productModelUpdater,
            $authorizationChecker,
            $productModelFilter,
            ['categories']
        );
    }

    function it_implements_a_filter_interface()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GrantedProductModelUpdater::class);
    }

    function it_throws_an_exception_if_it_updates_another_entity(\stdClass $entity)
    {
        $this->shouldThrow(InvalidObjectException::class)->during(
            'update', [$entity, []]
        );
    }

    function it_doesnt_check_for_granted_fields_if_the_product_model_doesnt_exist_yet(
        $productModelUpdater,
        $authorizationChecker,
        ProductModelInterface $productModel
    ) {
        $data = [
            'values' =>  [
                'a_text' => [
                    ['data' => 'data', 'locale' => null, 'scope' => null]
                ]
            ]
        ];

        $productModel->getId()->willReturn(null);

        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->shouldNotBeCalled();
        $productModelUpdater->update($productModel, $data, [])->shouldBeCalled();

        $this->update($productModel, $data);
    }

    function it_filters_fields_on_a_viewable_product(
        $productModelUpdater,
        $authorizationChecker,
        $productModelFilter,
        ProductModelInterface $productModel
    ) {
        $data = [
            'values' =>  [
                'a_text' => [
                    ['data' => 'data', 'locale' => null, 'scope' => null]
                ]
            ]
        ];

        $productModel->getId()->willReturn(1);
        $productModel->getCode()->willReturn('product_model');

        $authorizationChecker->isGranted([Attributes::EDIT], $productModel)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->willReturn(true);
        $productModelFilter->filter($productModel, [
            'values' =>  [
                'a_text' => [
                    ['data' => 'data', 'locale' => null, 'scope' => null]
                ]
            ]
        ])->willReturn(['a_text']);

        $productModelUpdater->update($productModel, $data, [])->shouldNotBeCalled();
        $this->shouldThrow(ResourceAccessDeniedException::class)->during('update', [$productModel, $data]);
    }

    function it_does_not_try_to_filter_fields_if_user_has_edit_permission(
        $productModelUpdater,
        $authorizationChecker,
        $productModelFilter,
        ProductModelInterface $productModel
    ) {
        $data = [
            'values' =>  [
                'a_text' => [
                    ['data' => 'data', 'locale' => null, 'scope' => null]
                ]
            ]
        ];
        $productModel->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::EDIT], $productModel)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->willReturn(true);
        $productModelFilter->filter($productModel, $data)->shouldNotBeCalled();

        $productModelUpdater->update($productModel, $data, [])->shouldBeCalled();
        $this->update($productModel, $data, []);
    }
}
