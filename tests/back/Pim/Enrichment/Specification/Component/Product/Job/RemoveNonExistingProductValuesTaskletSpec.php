<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
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
        GetAttributes $getAttributes,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $getProductAndProductModelIdsWithValues,
            $getAttributes,
            $productRepository,
            $productModelRepository,
            $productSaver,
            $productModelSaver,
            $entityManagerClearer,
            10
        );
        $this->setStepExecution($stepExecution);
    }

    function it_removes_non_existing_product_values_from_attribute_code_and_options(
        GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdsWithValues,
        GetAttributes $getAttributes,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        EntityManagerClearerInterface $entityManagerClearer,
        StepExecution $stepExecution
    ) {
        $jobParameter = new JobParameters([
            'attribute_code' => 'color',
            'attribute_options' => ['red', 'blue'],
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameter);

        $attribute = $this->createAttribute();
        $getAttributes->forCode('color')->willReturn($attribute);
        $getProductAndProductModelIdsWithValues->forAttributeAndValues('color', 'option', ['red', 'blue'])->willReturn(
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

        $productSaver->saveAll([$product1, $product2], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$productModel], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([], ['force_save' => true])->shouldBeCalled();
        $entityManagerClearer->clear()->shouldBeCalled();

        $this->execute();
    }

    function it_throws_an_exception_when_filter_concerns_an_unknown_attribute(
        GetAttributes $getAttributes,
        StepExecution $stepExecution
    ) {
        $jobParameter = new JobParameters([
            'attribute_code' => 'color',
            'attribute_options' => ['red', 'blue'],
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameter);

        $getAttributes->forCode('color')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('The "color" attribute code was not found'))
            ->during('execute');
    }

    protected function createAttribute(): Attribute
    {
        return new Attribute(
            'color',
            'pim_catalog_simpleselect',
            [],
            false,
            false,
            null,
            null,
            null,
            'option',
            []
        );
    }
}
