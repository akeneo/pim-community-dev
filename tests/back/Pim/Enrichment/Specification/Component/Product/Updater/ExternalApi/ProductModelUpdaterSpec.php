<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ExternalApi\ProductModelUpdater;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class ProductModelUpdaterSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_an_object_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_is_an_external_api_product_model_updater()
    {
        $this->shouldHaveType(ProductModelUpdater::class);
    }

    function it_throw_an_exception_when_updating_anything_but_a_product_model()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                ProductModelInterface::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_throw_an_exception_when_trying_to_unset_parent()
    {
        $productModel = new ProductModel();
        $productModel->setParent(new ProductModel());

        $this->shouldThrow(
            ImmutablePropertyException::immutableProperty(
                'parent',
                'NULL',
                ProductModelInterface::class
            )
        )->during('update', [$productModel, ['parent' => null]]);
    }

    function it_throws_an_exception_when_trying_to_unset_the_family_variant()
    {
        $this->shouldThrow(
            InvalidPropertyException::valueNotEmptyExpected(
                'family_variant',
                ProductModelInterface::class
            )
        )->during('update', [new ProductModel(), ['family_variant' => null]]);
    }

    function it_throws_an_exception_if_provided_family_does_not_match_the_family_variant_one($updater)
    {
        $data = [
            'family' => 'family_A',
            'family_variant' => 'a_family_variant_not_belonging_to_family_A',
        ];
        $productModel = new ProductModel();

        $updater->update($productModel, ['family_variant' => 'a_family_variant_not_belonging_to_family_A'], [])
            ->will(function() use ($productModel) {
                $family = new Family();
                $family->setCode('some_family');
                $familyVariant = new FamilyVariant();
                $familyVariant->setCode('a_family_variant_not_belonging_to_family_A');
                $familyVariant->setFamily($family);

                $productModel->setFamilyVariant($familyVariant);
            });

        $this->shouldThrow(
            InvalidPropertyException::expected(
                sprintf(
                    'The family variant "%s" is not a variant of the family "%s".',
                    'a_family_variant_not_belonging_to_family_A',
                    'family_A'
                ),
                ProductModelInterface::class
            )
        )->during('update', [$productModel, $data]);
    }
}
