<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Updater\ProductModelUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelUpdaterSpec extends ObjectBehavior
{
    function let(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        IdentifiableObjectRepositoryInterface $familyVariantRepository
    ) {
        $this->beConstructedWith(
            $propertySetter,
            $valuesUpdater,
            $familyVariantRepository,
            ['categories'],
            ['identifier']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_product_model(
        $familyVariantRepository,
        $propertySetter,
        $valuesUpdater,
        ProductModelInterface $productModel,
        FamilyVariantInterface $familyVariant
    ) {
        $propertySetter->setData($productModel, 'categories', ['tshirt'])->shouldBeCalled();
        $productModel->setIdentifier('product_model_identifier')->shouldBeCalled();

        $familyVariantRepository->findOneByIdentifier('clothing_color_size')->willreturn($familyVariant);
        $productModel->setFamilyVariant($familyVariant)->shouldBeCalled();

        $valuesUpdater->update($productModel, [
            'name' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt',
            ],
            'description' => [
                'locale' => 'fr_FR',
                'scope' => 'null',
                'data' => 'T-shirt super beau',
            ],
        ], [])->shouldBeCalled();

        $this->update($productModel, [
            'identifier' => 'product_model_identifier',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
            'family_variant' => 'clothing_color_size',
        ])->shouldReturn($this);
    }

    function it_throws_an_exception_if_the_family_variant_code_is_invalid(
        $familyVariantRepository,
        ProductModelInterface $productModel
    ) {
        $familyVariantRepository->findOneByIdentifier('wrong_code')->willreturn(null);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [$productModel, [
            'family_variant' => 'wrong_code'
        ]]);
    }

    function it_only_works_with_product_model(ProductInterface $product)
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [$product, [], []]);
    }
}
