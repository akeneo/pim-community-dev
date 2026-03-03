<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResults;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class RemoveNonExistingProductValuesTaskletSpec extends ObjectBehavior
{
    function let(
        GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope $getProductAndProductModelIdsWithValues,
        GetAttributes $getAttributes,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
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
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
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
        $identifierResults1 = new IdentifierResults();
        $identifierResults2 = new IdentifierResults();
        $product1 = new Product();
        $product2 = new Product();
        $productModel = new ProductModel();

        $identifierResults1->add('code1', ProductInterface::class, 'product_' . $product1->getUuid()->toString());
        $identifierResults1->add('code2',  ProductInterface::class, 'product_' . $product2->getUuid()->toString());
        $identifierResults2->add('code3',  ProductModelInterface::class, 'product_model_' . $productModel->getCode());
        $getProductAndProductModelIdsWithValues->forAttributeAndValues('color', 'option', ['red', 'blue'])->willYield(
            [$identifierResults1, $identifierResults2]
        );

        $productRepository->getItemsFromUuids([$product1->getUuid()->toString(), $product2->getUuid()->toString()])->willReturn([$product1, $product2]);
        $productModelRepository->getItemsFromIdentifiers(['code3'])->willReturn([$productModel]);

        $productSaver->saveAll([$product1, $product2], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$productModel], ['force_save' => true])->shouldBeCalled();
        $entityManagerClearer->clear()->shouldBeCalledTimes(2);

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
