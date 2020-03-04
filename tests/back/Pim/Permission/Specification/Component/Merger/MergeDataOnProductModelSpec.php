<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;

class MergeDataOnProductModelSpec extends ObjectBehavior
{
    function let(
        NotGrantedDataMergerInterface $valuesMerger,
        NotGrantedDataMergerInterface $categoryMerger,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith([$valuesMerger, $categoryMerger], $productModelRepository);
    }

    function it_return_filtered_product_model_when_it_is_new(ProductModelInterface $filteredProductModel)
    {
        $this->merge($filteredProductModel)->shouldReturn($filteredProductModel);
    }

    function it_applies_values_from_filtered_product_model_to_full_product_model(
        $valuesMerger,
        $categoryMerger,
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        FamilyVariantInterface $familyVariant,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW,
        ArrayCollection $productModels,
        ArrayCollection $products,
        \ArrayIterator $iteratorProductModels,
        \ArrayIterator $iteratorProducts
    ) {
        $parent->getId()->willReturn(1);
        $productModelRepository->find(1)->willReturn($parentInUoW);
        $filteredProductModel->getParent()->willReturn($parentInUoW);
        $filteredProductModel->setParent($parentInUoW)->shouldBeCalled();

        $filteredProductModel->getCode()->willReturn('my_code');
        $filteredProductModel->getFamilyVariant()->willReturn($familyVariant);
        $filteredProductModel->getParent()->willReturn($parent);
        $filteredProductModel->getProductModels()->willReturn($productModels);
        $filteredProductModel->getProducts()->willReturn($products);

        $productModels->getIterator()->willReturn($iteratorProductModels);
        $products->getIterator()->willReturn($iteratorProducts);

        $fullProductModel->setCode('my_code')->shouldBeCalled();
        $fullProductModel->setFamilyVariant($familyVariant)->shouldBeCalled();
        $fullProductModel->setParent($parent)->shouldBeCalled();

        $valuesMerger->merge($filteredProductModel, $fullProductModel)->willReturn($fullProductModel);
        $categoryMerger->merge($filteredProductModel, $fullProductModel)->willReturn($fullProductModel);

        $this->merge($filteredProductModel, $fullProductModel)->shouldReturn($fullProductModel);
    }

    function it_sets_parent_from_unit_of_work_if_it_is_new(
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $parent,
        ProductModelInterface $parentInUoW
    ) {
        $parent->getId()->willReturn(1);
        $filteredProductModel->getParent()->willReturn($parent);
        $productModelRepository->find(1)->willReturn($parentInUoW);

        $filteredProductModel->setParent($parentInUoW)->shouldBeCalled();

        $this->merge($filteredProductModel)->shouldReturn($filteredProductModel);
    }

    function it_throws_an_exception_if_filtered_subject_is_not_a_product_model()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductModelInterface::class))
            ->during('merge', [new \stdClass(), new ProductModel()]);
    }

    function it_throws_an_exception_if_full_subject_is_not_a_product_model()
    {
        $this->shouldThrow(InvalidObjectException::objectExpected(ClassUtils::getClass(new \stdClass()), ProductModelInterface::class))
            ->during('merge', [new ProductModel(), new \stdClass()]);
    }
}
