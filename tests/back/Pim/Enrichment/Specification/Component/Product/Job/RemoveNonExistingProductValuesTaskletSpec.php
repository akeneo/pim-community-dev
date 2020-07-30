<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class RemoveNonExistingProductValuesTaskletSpec extends ObjectBehavior
{
    function let(
        GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdsWithValues,
        AttributeRepositoryInterface $attributeRepository,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $getProductAndProductModelIdsWithValues,
            $attributeRepository,
            $productRepository,
            $productModelRepository,
            $productSaver,
            $productModelSaver,
            $entityManagerClearer,
            10
        );
        $this->setStepExecution($stepExecution);
    }

    function it_removes_non_existing_product_values_from_filters(
        GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdsWithValues,
        AttributeRepositoryInterface $attributeRepository,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        StepExecution $stepExecution
    ) {
        $jobParameter = new JobParameters(['filters' => [
            [
                'field' => 'color',
                'operator' => Operators::IN_LIST,
                'values' => ['red', 'blue'],
            ]
        ]]);
        $stepExecution->getJobParameters()->willReturn($jobParameter);

        $attribute = new Attribute();
        $attributeRepository->findOneByIdentifier('color')->willReturn($attribute);
        $getProductAndProductModelIdsWithValues->forAttributeAndValues($attribute, ['red', 'blue'])->willReturn(
            new \ArrayIterator([
                ['code1', 'code2'],
                ['code3'],
            ])
        );

        $product1 = new Product();
        $product2 = new Product();
        $productModel = new ProductModel();
        $productRepository->getItemsFromIdentifiers(['code1', 'code2'])->willReturn([$product1, $product2]);
        $productModelRepository->getItemsFromIdentifiers(['code1', 'code2'])->willReturn([]);
        $productRepository->getItemsFromIdentifiers(['code3'])->willReturn([]);
        $productModelRepository->getItemsFromIdentifiers(['code3'])->willReturn([$productModel]);

        $productSaver->saveAll([$product1, $product2])->shouldBeCalled();
        $productModelSaver->saveAll([$productModel])->shouldBeCalled();
        $productSaver->saveAll([])->shouldBeCalled();
        $productModelSaver->saveAll([])->shouldBeCalled();
        $entityManagerClearer->clear()->shouldBeCalled();

        $this->execute();
    }

    function it_throws_an_exception_when_filter_concerns_an_unknown_attribute(
        AttributeRepositoryInterface $attributeRepository,
        StepExecution $stepExecution
    ) {
        $jobParameter = new JobParameters(['filters' => [
            [
                'field' => 'color',
                'operator' => Operators::IN_LIST,
                'values' => ['red', 'blue'],
            ]
        ]]);
        $stepExecution->getJobParameters()->willReturn($jobParameter);

        $attributeRepository->findOneByIdentifier('color')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('The "color" attribute code was not found'))
            ->during('execute');
    }

    function it_throws_an_exception_when_filter_is_not_well_formed(
        AttributeRepositoryInterface $attributeRepository,
        StepExecution $stepExecution
    ) {
        $jobParameter = new JobParameters(['filters' => [
            [
                'code' => 'color',
            ]
        ]]);
        $stepExecution->getJobParameters()->willReturn($jobParameter);

        $attributeRepository->findOneByIdentifier('color')->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('execute');
    }
}
