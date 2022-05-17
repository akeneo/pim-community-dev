<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet\CleanTableValuesWithDeletedOptionsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

class CleanTableValuesWithDeletedOptionsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetAttributes $getAttributes,
        GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('attribute_code')->willReturn('nutrition');
        $jobParameters->get('removed_options_per_column_code')->willReturn([
            'ingredients' => ['salt', 'sugar'],
        ]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $this->beConstructedWith(
            $pqbFactory,
            $getAttributes,
            $getChannelCodeWithLocaleCodes,
            $productSaver,
            $productModelSaver,
            1000
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CleanTableValuesWithDeletedOptionsTasklet::class);
    }

    function it_cleans_the_values_of_a_non_scopable_non_localizable_table_attribute(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetAttributes $getAttributes,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        StepExecution $stepExecution,
        ProductQueryBuilderInterface $rootProductModelQueryBuilder,
        ProductQueryBuilderInterface $subProductModelQueryBuilder,
        ProductQueryBuilderInterface $productQueryBuilder,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $productCursor,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel,
        ProductInterface $product
    ) {
        $getAttributes->forCode('nutrition')->willReturn($this->createAttribute('nutrition'));
        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null],
            ],
        ])->shouldBeCalledOnce()->willReturn($rootProductModelQueryBuilder);
        $this->thenTheFilterIsAddedForTheLocaleScope($rootProductModelQueryBuilder, ['locale' => null, 'scope' => null]);
        $this->thenPqbIsExecutedAndCursorIsIterate($rootProductModelQueryBuilder, $rootProductModelCursor, $rootProductModel);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null],
            ],
        ])->shouldBeCalledOnce()->willReturn($subProductModelQueryBuilder);
        $this->thenTheFilterIsAddedForTheLocaleScope($subProductModelQueryBuilder, ['locale' => null, 'scope' => null]);
        $this->thenPqbIsExecutedAndCursorIsIterate($subProductModelQueryBuilder, $subProductModelCursor, $subProductModel);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
            ],
        ])->shouldBeCalledOnce()->willReturn($productQueryBuilder);
        $this->thenTheFilterIsAddedForTheLocaleScope($productQueryBuilder, ['locale' => null, 'scope' => null]);
        $this->thenPqbIsExecutedAndCursorIsIterate($productQueryBuilder, $productCursor, $product);

        $productModelSaver->saveAll([$rootProductModel], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$subProductModel], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([$product], ['force_save' => true])->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(3);

        $this->execute();
    }

    function it_cleans_the_values_of_a_scopable_localizable_table_attribute(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetAttributes $getAttributes,
        GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        StepExecution $stepExecution,
        ProductQueryBuilderInterface $rootProductModelQueryBuilder1,
        ProductQueryBuilderInterface $rootProductModelQueryBuilder2,
        ProductQueryBuilderInterface $rootProductModelQueryBuilder3,
        ProductQueryBuilderInterface $subProductModelQueryBuilder1,
        ProductQueryBuilderInterface $subProductModelQueryBuilder2,
        ProductQueryBuilderInterface $subProductModelQueryBuilder3,
        ProductQueryBuilderInterface $productQueryBuilder1,
        ProductQueryBuilderInterface $productQueryBuilder2,
        ProductQueryBuilderInterface $productQueryBuilder3,
        CursorInterface $rootProductModelCursor1,
        CursorInterface $rootProductModelCursor2,
        CursorInterface $rootProductModelCursor3,
        CursorInterface $subProductModelCursor1,
        CursorInterface $subProductModelCursor2,
        CursorInterface $subProductModelCursor3,
        CursorInterface $productCursor1,
        CursorInterface $productCursor2,
        CursorInterface $productCursor3,
        ProductModelInterface $rootProductModel1,
        ProductModelInterface $rootProductModel2,
        ProductModelInterface $rootProductModel3,
        ProductModelInterface $subProductModel,
        ProductInterface $product
    ) {
        $getAttributes->forCode('nutrition')->willReturn($this->createAttribute('nutrition', AttributeTypes::TABLE, true, true));
        $getChannelCodeWithLocaleCodes->findAll()->willReturn([
            [
                'channelCode' => 'ecommerce',
                'localeCodes' => ['en_US', 'fr_FR'],
            ],
            [
                'channelCode' => 'mobile',
                'localeCodes' => ['en_US'],
            ],
        ]);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'parent', 'operator' => Operators::IS_EMPTY, 'value' => null],
            ],
        ])->shouldBeCalledTimes(3)->willReturn($rootProductModelQueryBuilder1, $rootProductModelQueryBuilder2, $rootProductModelQueryBuilder3);
        $this->thenTheFilterIsAddedForTheLocaleScope($rootProductModelQueryBuilder1, ['locale' => 'en_US', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($rootProductModelQueryBuilder2, ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($rootProductModelQueryBuilder3, ['locale' => 'en_US', 'scope' => 'mobile']);
        $this->thenPqbIsExecutedAndCursorIsIterate($rootProductModelQueryBuilder1, $rootProductModelCursor1, $rootProductModel1);
        $this->thenPqbIsExecutedAndCursorIsIterate($rootProductModelQueryBuilder2, $rootProductModelCursor2, $rootProductModel2);
        $this->thenPqbIsExecutedAndCursorIsIterate($rootProductModelQueryBuilder3, $rootProductModelCursor3, $rootProductModel3);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null],
            ],
        ])->shouldBeCalledTimes(3)->willReturn($subProductModelQueryBuilder1, $subProductModelQueryBuilder2, $subProductModelQueryBuilder3);
        $this->thenTheFilterIsAddedForTheLocaleScope($subProductModelQueryBuilder1, ['locale' => 'en_US', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($subProductModelQueryBuilder2, ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($subProductModelQueryBuilder3, ['locale' => 'en_US', 'scope' => 'mobile']);
        $this->thenPqbIsExecutedAndCursorIsIterate($subProductModelQueryBuilder1, $subProductModelCursor1, $subProductModel);
        $this->thenPqbIsExecutedAndCursorIsIterate($subProductModelQueryBuilder2, $subProductModelCursor2, $subProductModel);
        $this->thenPqbIsExecutedAndCursorIsIterate($subProductModelQueryBuilder3, $subProductModelCursor3, $subProductModel);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
            ],
        ])->shouldBeCalledTimes(3)->willReturn($productQueryBuilder1, $productQueryBuilder2, $productQueryBuilder3);
        $this->thenTheFilterIsAddedForTheLocaleScope($productQueryBuilder1, ['locale' => 'en_US', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($productQueryBuilder2, ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->thenTheFilterIsAddedForTheLocaleScope($productQueryBuilder3, ['locale' => 'en_US', 'scope' => 'mobile']);
        $this->thenPqbIsExecutedAndCursorIsIterate($productQueryBuilder1, $productCursor1, $product);
        $this->thenPqbIsExecutedAndCursorIsIterate($productQueryBuilder2, $productCursor2, $product);
        $this->thenPqbIsExecutedAndCursorIsIterate($productQueryBuilder3, $productCursor3, $product);

        $productModelSaver->saveAll([$rootProductModel1], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel2], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$rootProductModel3], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$subProductModel], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([$product], ['force_save' => true])->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(9);

        $this->execute();
    }

    private function thenTheFilterIsAddedForTheLocaleScope(
        ProductQueryBuilderInterface $rootProductModelQueryBuilder,
        array $localeScopeOptions
    ): void {
        $rootProductModelQueryBuilder->addFilter(
            'nutrition',
            Operators::IN_LIST,
            ['column' => 'ingredients', 'value' => ['salt', 'sugar']],
            $localeScopeOptions
        )->shouldBeCalled()->willReturn($rootProductModelQueryBuilder);
    }

    private function thenPqbIsExecutedAndCursorIsIterate(
        ProductQueryBuilderInterface $productQueryBuilder,
        CursorInterface $cursor,
        $productOrProductModel
    ): void {
        $productQueryBuilder->execute()->shouldBeCalledOnce()->willReturn($cursor);
        $cursor->rewind()->shouldBeCalledOnce();
        $cursor->valid()->shouldBeCalledTimes(2)->willReturn(true, false);
        $cursor->next()->shouldBeCalledOnce();
        $cursor->current()->shouldBeCalledOnce()->willReturn($productOrProductModel);
    }

    private function createAttribute(
        string $code,
        string $type = AttributeTypes::TABLE,
        bool $scopable = false,
        bool $localizable = false,
        array $availableLocaleCodes = []
    ): Attribute {
        return new Attribute(
            $code,
            $type,
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'backend_type',
            $availableLocaleCodes
        );
    }
}
