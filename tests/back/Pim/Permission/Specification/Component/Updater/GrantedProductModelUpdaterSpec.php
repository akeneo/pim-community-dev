<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Updater\GrantedProductModelUpdater;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;


class GrantedProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productModelUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productModelFilter,
        FilterInterface $productModelFieldFilter,
        FilterInterface $productModelAssociationFilter
    ) {
        $this->beConstructedWith(
            $productModelUpdater,
            $authorizationChecker,
            $productModelFilter,
            ['categories'],
            $productModelFieldFilter,
            $productModelAssociationFilter,
            ['associations', 'quantified_associations']
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

        $authorizationChecker->isGranted([Attributes::OWN], $productModel)->willReturn(false);
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

    public function it_filters_fields_on_a_draft(
        $productModelUpdater,
        $authorizationChecker,
        $productModelFieldFilter,
        $productModelAssociationFilter,
        ProductModelInterface $productModel
    ) {
        $data = [
            'values' =>  ['a_text' => [['data' => 'data', 'locale' => null, 'scope' => null]]],
            'associations' => ['X_SELL' => ['products' => ['product_a', 'product_b']]]
        ];
        $productModel->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $productModel)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $productModel)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->willReturn(true);
        $productModelFieldFilter->filter($productModel, ['values' => $data['values']])->willReturn([]);
        $productModelAssociationFilter
            ->filter($productModel, ['associations' => ['X_SELL' => ['products' => ['product_a', 'product_b']]]])
            ->willReturn([]);

        $productModelUpdater->update($productModel, $data, [])->shouldBeCalled();
        $this->update($productModel, $data, []);
    }

    function it_throws_an_exception_if_user_tries_to_update_fields_on_a_draft(
        $productModelUpdater,
        $authorizationChecker,
        $productModelFieldFilter,
        $productModelAssociationFilter,
        ProductModelInterface $productModel
    ) {
        $data = [
            'categories' => ['cameras'],
            'associations' => ['X_SELL' => ['products' => ['product_a', 'product_b']]]
        ];
        $productModel->getId()->willReturn(1);

        $authorizationChecker->isGranted([Attributes::OWN], $productModel)->willReturn(false);
        $authorizationChecker->isGranted([Attributes::EDIT], $productModel)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->willReturn(true);
        $productModelFieldFilter
            ->filter($productModel, ['categories' => ['cameras']])
            ->willReturn(['categories' => ['cameras']]);
        $productModelAssociationFilter
            ->filter($productModel, ['associations' => ['X_SELL' => ['products' => ['product_a', 'product_b']]]])
            ->willReturn([]);

        $productModelUpdater->update($productModel, $data, [])->shouldNotBeCalled();

        $this->shouldThrow(InvalidArgumentException::class)->during('update', [$productModel, $data, []]);
    }

    function it_does_not_try_to_filter_fields_if_user_has_edit_and_own_permissions(
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

        $authorizationChecker->isGranted([Attributes::OWN], $productModel)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::EDIT], $productModel)->willReturn(true);
        $authorizationChecker->isGranted([Attributes::VIEW], $productModel)->willReturn(true);
        $productModelFilter->filter($productModel, $data)->shouldNotBeCalled();

        $productModelUpdater->update($productModel, $data, [])->shouldBeCalled();
        $this->update($productModel, $data, []);
    }
}
