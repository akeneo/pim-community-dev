<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
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
            $productModelSaver
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
        $rootProductModelQueryBuilder->addFilter(
            'nutrition',
            Operators::IN_LIST,
            ['column' => 'ingredients', 'value' => ['salt', 'sugar']],
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled()->willReturn($rootProductModelQueryBuilder);
        $rootProductModelQueryBuilder->execute()->shouldBeCalled()->willReturn($rootProductModelCursor);
        $rootProductModelCursor->rewind()->shouldBeCalledOnce();
        $rootProductModelCursor->valid()->shouldBeCalledTimes(2)->willReturn(true, false);
        $rootProductModelCursor->next()->shouldBeCalledOnce();
        $rootProductModelCursor->current()->shouldBeCalledOnce()->willReturn($rootProductModel);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductModelInterface::class],
                ['field' => 'parent', 'operator' => Operators::IS_NOT_EMPTY, 'value' => null],
            ],
        ])->shouldBeCalledOnce()->willReturn($subProductModelQueryBuilder);
        $subProductModelQueryBuilder->addFilter(
            'nutrition',
            Operators::IN_LIST,
            ['column' => 'ingredients', 'value' => ['salt', 'sugar']],
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled()->willReturn($subProductModelQueryBuilder);
        $subProductModelQueryBuilder->execute()->shouldBeCalledOnce()->willReturn($subProductModelCursor);
        $subProductModelCursor->rewind()->shouldBeCalledOnce();
        $subProductModelCursor->valid()->shouldBeCalledTimes(2)->willReturn(true, false);
        $subProductModelCursor->next()->shouldBeCalledOnce();
        $subProductModelCursor->current()->shouldBeCalledOnce()->willReturn($subProductModel);

        $pqbFactory->create([
            'filters' => [
                ['field' => 'entity_type', 'operator' => Operators::EQUALS, 'value' => ProductInterface::class],
            ],
        ])->shouldBeCalledOnce()->willReturn($productQueryBuilder);
        $productQueryBuilder->addFilter(
            'nutrition',
            Operators::IN_LIST,
            ['column' => 'ingredients', 'value' => ['salt', 'sugar']],
            ['locale' => null, 'scope' => null]
        )->shouldBeCalled()->willReturn($productQueryBuilder);
        $productQueryBuilder->execute()->shouldBeCalledOnce()->willReturn($productCursor);
        $productCursor->rewind()->shouldBeCalledOnce();
        $productCursor->valid()->shouldBeCalledTimes(2)->willReturn(true, false);
        $productCursor->next()->shouldBeCalledOnce();
        $productCursor->current()->shouldBeCalledOnce()->willReturn($product);

        $productModelSaver->saveAll([$rootProductModel], ['force_save' => true])->shouldBeCalled();
        $productModelSaver->saveAll([$subProductModel], ['force_save' => true])->shouldBeCalled();
        $productSaver->saveAll([$product], ['force_save' => true])->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(3);

        $this->execute();
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
