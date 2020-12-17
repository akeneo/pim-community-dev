<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Processor\Normalization;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetAssetMainMediaValuesInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Processor\BulkAssetMediaFetcher;
use Akeneo\Pim\Enrichment\AssetManager\Component\Processor\Normalization\EntityWithAssetMediaProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EntityWithAssetMediaProcessorSpec extends ObjectBehavior
{
     function let(
         ItemProcessorInterface $decoratedItemProcessor,
         GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
         GetAttributes $getAttributes,
         BulkAssetMediaFetcher $bulkAssetMediaFetcher
     ) {
         $getAttributes->forCode('text')->willReturn($this->buildAttribute('text', AttributeTypes::TEXT));
         $getAttributes->forCode('asset_attr1')
             ->willReturn($this->buildAttribute('asset_attr1', AssetCollectionType::ASSET_COLLECTION, 'asset_family1'));
         $getAttributes->forCode('asset_attr2')
             ->willReturn($this->buildAttribute('asset_attr2', AssetCollectionType::ASSET_COLLECTION, 'asset_family2'));
         $getAttributes->forCode('asset_attr3')
             ->willReturn($this->buildAttribute('asset_attr3', AssetCollectionType::ASSET_COLLECTION, 'asset_family3'));

         $this->beConstructedWith(
             $decoratedItemProcessor,
             $getAssetMainMediaValues,
             $getAttributes,
             $bulkAssetMediaFetcher,
         );
     }

     function it_can_be_instantiated()
     {
         $this->shouldBeAnInstanceOf(EntityWithAssetMediaProcessor::class);
     }

     function it_is_an_item_processor()
     {
         $this->shouldImplement(ItemProcessorInterface::class);
     }

     function it_does_nothing_more_when_it_processes_without_media(ItemProcessorInterface $decoratedItemProcessor)
     {
         $this->givenAJobExecutionWihoutMedia();

         $product = new Product();
         $productStandard = ['identifier' => 'foo', 'values' => []];
         $decoratedItemProcessor->process($product)->willReturn($productStandard);

         $this->process($product)->shouldBe($productStandard);
     }

    function it_throws_an_exception_with_a_bad_entity()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }

     function it_processes_a_product_with_media_and_export_filters(
         ItemProcessorInterface $decoratedItemProcessor,
         GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
         BulkAssetMediaFetcher $bulkAssetMediaFetcher
     ) {
         $this->givenAJobExecutionWihMediaAndExportFilters();
         $product = new Product();
         $product->setIdentifier('product1');

         $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
         $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02']],
             [
                 ['locale' => 'en_US', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg']],
                 ['locale' => null, 'channel' => 'mobile', 'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr1'
         )->willReturn([
             'files/product1/asset_attr1/file_01.jpg',
             'files/product1/asset_attr1/file_02.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath3', 'originalFilename' => 'file_11.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr2'
         )->willReturn([
             'files/product1/asset_attr2/en_US/file_11.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_12.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr2'
         )->willReturn([
             'files/product1/asset_attr2/fr_FR/file_12.jpg',
         ]);
         $bulkAssetMediaFetcher->getErrors()->willReturn([]);

         $result = $this->process($product);
         $result->shouldBe($this->getExpectedStandardFormatWithExportFilters('product1'));
     }

    function it_processes_a_product_with_media_and_quick_export_filters(
        ItemProcessorInterface $decoratedItemProcessor,
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
        BulkAssetMediaFetcher $bulkAssetMediaFetcher
    ) {
        $this->givenAJobExecutionWihMediaAndQuickExportFilters();
        $product = new Product();
        $product->setIdentifier('product1');

        $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
        $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02']],
            [
                ['locale' => 'en_US', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg']],
                ['locale' => 'de_DE', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'bad_locale.jpg']],
                ['locale' => null, 'channel' => 'mobile', 'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg']],
            ],
            '/tmp/',
            'product1',
            'asset_attr1'
        )->willReturn([
            'files/product1/asset_attr1/file_01.jpg',
            'files/product1/asset_attr1/bad_locale.jpg',
            'files/product1/asset_attr1/file_02.jpg',
        ]);
        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12']],
            [
                ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath3', 'originalFilename' => 'file_11.jpg']],
            ],
            '/tmp/',
            'product1',
            'asset_attr2'
        )->willReturn([
            'files/product1/asset_attr2/en_US/file_11.jpg',
        ]);
        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13']],
            [
                ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_12.jpg']],
            ],
            '/tmp/',
            'product1',
            'asset_attr2'
        )->willReturn([
            'files/product1/asset_attr2/fr_FR/file_12.jpg',
        ]);
        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14']],
            [
                ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_13.jpg']],
            ],
            '/tmp/',
            'product1',
            'asset_attr2'
        )->willReturn([
            'files/product1/asset_attr2/de_DE/file_13.jpg',
        ]);
        $bulkAssetMediaFetcher->getErrors()->willReturn([]);

        $result = $this->process($product);
        $result->shouldBe($this->getExpectedStandardFormatWithQuickExportFilters('product1'));
    }

     function it_processes_a_product_with_media_without_filters(
         ItemProcessorInterface $decoratedItemProcessor,
         GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
         BulkAssetMediaFetcher $bulkAssetMediaFetcher
     ) {
         $this->givenAJobExecutionWihMediaAndWithoutFilters();
         $product = new Product();
         $product->setIdentifier('product1');

         $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
         $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02']],
             [
                 ['locale' => 'en_US', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg']],
                 ['locale' => 'de_DE', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'bad_locale.jpg']],
                 ['locale' => null, 'channel' => 'mobile', 'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg']],
                 ['locale' => null, 'channel' => 'ecommerce', 'data' => ['filePath' => 'filePath2', 'originalFilename' => 'bad_scope.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr1'
         )->willReturn([
             'files/product1/asset_attr1/file_01.jpg',
             'files/product1/asset_attr1/bad_locale.jpg',
             'files/product1/asset_attr1/file_02.jpg',
             'files/product1/asset_attr1/bad_scope.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath3', 'originalFilename' => 'file_11.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr2'
         )->willReturn([
             'files/product1/asset_attr2/en_US/file_11.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_12.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr2'
         )->willReturn([
             'files/product1/asset_attr2/fr_FR/file_12.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_13.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr2'
         )->willReturn([
             'files/product1/asset_attr2/de_DE/file_13.jpg',
         ]);
         $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
             ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22']],
             [
                 ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_14.jpg']],
             ],
             '/tmp/',
             'product1',
             'asset_attr3'
         )->willReturn([
             'files/product1/asset_attr3/ecommerce/file_14.jpg',
         ]);
         $bulkAssetMediaFetcher->getErrors()->willReturn([]);

         $result = $this->process($product);
         $result->shouldBe($this->getExpectedStandardFormatWithoutFilter('product1'));
     }

    function it_processes_a_product_model_with_media(
        ItemProcessorInterface $decoratedItemProcessor,
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues,
        BulkAssetMediaFetcher $bulkAssetMediaFetcher
    ) {
        $this->givenAJobExecutionWihMediaAndExportFilters();
        $productModel = new ProductModel();
        $productModel->setCode('product_model1');

        $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $productModel);
        $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02']],
            [
                ['locale' => 'en_US', 'channel' => null, 'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg']],
                ['locale' => null, 'channel' => 'mobile', 'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg']],
            ],
            '/tmp/',
            'product_model1',
            'asset_attr1'
        )->willReturn([
            'files/product_model1/asset_attr1/file_01.jpg',
            'files/product_model1/asset_attr1/file_02.jpg',
        ]);
        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12']],
            [
                ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath3', 'originalFilename' => 'file_11.jpg']],
            ],
            '/tmp/',
            'product_model1',
            'asset_attr2'
        )->willReturn([
            'files/product_model1/asset_attr2/en_US/file_11.jpg',
        ]);
        $bulkAssetMediaFetcher->fetchAllForAssetRawValuesAndReturnPaths(
            ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13']],
            [
                ['locale' => null, 'channel' => null, 'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_12.jpg']],
            ],
            '/tmp/',
            'product_model1',
            'asset_attr2'
        )->willReturn([
            'files/product_model1/asset_attr2/fr_FR/file_12.jpg',
        ]);
        $bulkAssetMediaFetcher->getErrors()->willReturn([]);

        $result = $this->process($productModel);
        $result->shouldBe($this->getExpectedStandardFormatWithExportFilters('product_model1'));
    }

     private function givenAJobExecutionWihMediaAndExportFilters(): void
     {
         $jobExecution = new JobExecution();
         $jobExecution->setJobParameters(new JobParameters([
             'with_media' => true,
             'filters' => [
                 'structure' => [
                     'scope' => 'mobile',
                     'locales' => ['en_US', 'fr_FR'],
                 ],
             ],
         ]));
         $executionContext = new ExecutionContext();
         $executionContext->put(JobInterface::WORKING_DIRECTORY_PARAMETER, '/tmp/');
         $jobExecution->setExecutionContext($executionContext);
         $stepExecution = new StepExecution('name', $jobExecution);
         $this->setStepExecution($stepExecution);
     }

     private function givenAJobExecutionWihMediaAndQuickExportFilters(): void
     {
         $jobExecution = new JobExecution();
         $jobExecution->setJobParameters(new JobParameters([
             'with_media' => true,
             'filters' => [
                 [
                     'context' => [
                         'scope' => 'mobile',
                         'locale' => 'en_US',
                     ],
                 ],
             ],
         ]));
         $executionContext = new ExecutionContext();
         $executionContext->put(JobInterface::WORKING_DIRECTORY_PARAMETER, '/tmp/');
         $jobExecution->setExecutionContext($executionContext);
         $stepExecution = new StepExecution('name', $jobExecution);
         $this->setStepExecution($stepExecution);
     }

     private function givenAJobExecutionWihMediaAndWithoutFilters(): void
     {
         $jobExecution = new JobExecution();
         $jobExecution->setJobParameters(new JobParameters([
             'with_media' => true,
             'filters' => [],
         ]));
         $executionContext = new ExecutionContext();
         $executionContext->put(JobInterface::WORKING_DIRECTORY_PARAMETER, '/tmp/');
         $jobExecution->setExecutionContext($executionContext);
         $stepExecution = new StepExecution('name', $jobExecution);
         $this->setStepExecution($stepExecution);
     }

     private function givenAJobExecutionWihoutMedia(): void
     {
         $jobExecution = new JobExecution();
         $jobExecution->setJobParameters(new JobParameters(['with_media' => false]));
         $stepExecution = new StepExecution('name', $jobExecution);
         $this->setStepExecution($stepExecution);
     }

     private function givenAStandardFormatReturnedByDecoratedProcessor(
         ItemProcessorInterface $decoratedItemProcessor,
         $item
     ): void {
         $productStandard = [
             'identifier' => 'foo',
             'values' => [
                 'text' => [
                     ['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'a text'],
                     ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'un texte'],
                 ],
                 'asset_attr1' => [
                     ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02']],
                 ],
                 'asset_attr2' => [
                     ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12']],
                     ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13']],
                     ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14']],
                 ],
                 'asset_attr3' => [
                     ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                     ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22']],
                 ],
             ],
         ];
         $decoratedItemProcessor->process($item)->willReturn($productStandard);
     }

    private function givenAssetMainValuesReturnsSomeValues(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family1', ['assetCode01', 'assetCode02'])
            ->willReturn([
                'assetCode01' => [
                    [
                        'locale' => 'en_US',
                        'channel' => null,
                        'data' => ['filePath' => 'filePath1', 'originalFilename' => 'file_01.jpg'],
                    ],
                    [
                        'locale' => 'de_DE',
                        'channel' => null,
                        'data' => ['filePath' => 'filePath1', 'originalFilename' => 'bad_locale.jpg'],
                    ],
                ],
                'assetCode02' => [
                    [
                        'locale' => null,
                        'channel' => 'mobile',
                        'data' => ['filePath' => 'filePath2', 'originalFilename' => 'file_02.jpg'],
                    ],
                    [
                        'locale' => null,
                        'channel' => 'ecommerce',
                        'data' => ['filePath' => 'filePath2', 'originalFilename' => 'bad_scope.jpg'],
                    ],
                ],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family2', ['assetCode11', 'assetCode12'])
            ->willReturn([
                'assetCode11' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => 'filePath3', 'originalFilename' => 'file_11.jpg'],
                    ],
                ],
                'assetCode12' => [],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family2', ['assetCode13'])
            ->willReturn([
                'assetCode13' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_12.jpg'],
                    ],
                ],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family3', ['assetCode21'])
            ->willReturn([]);

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family2', ['assetCode14'])
            ->willReturn([
                'assetCode14' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_13.jpg'],
                    ],
                ],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family3', ['assetCode22'])
            ->willReturn([
                'assetCode22' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => 'filePath4', 'originalFilename' => 'file_14.jpg'],
                    ],
                ],
            ]);
    }

    private function getExpectedStandardFormatWithExportFilters(string $itemIdentifier): array
    {
        return [
            'identifier' => 'foo',
            'values' => [
                'text' => [
                    ['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'a text'],
                    ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'un texte'],
                ],
                'asset_attr1' => [
                    ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr1/file_01.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/file_02.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/en_US/file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/fr_FR/file_12.jpg',
                    ]],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14']],
                ],
                'asset_attr3' => [
                    ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22']],
                ],
            ],
        ];
    }

    private function getExpectedStandardFormatWithQuickExportFilters(string $itemIdentifier): array
    {
        return [
            'identifier' => 'foo',
            'values' => [
                'text' => [
                    ['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'a text'],
                    ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'un texte'],
                ],
                'asset_attr1' => [
                    ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr1/file_01.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/bad_locale.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/file_02.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/en_US/file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/fr_FR/file_12.jpg',
                    ]],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/de_DE/file_13.jpg',
                    ]],
                ],
                'asset_attr3' => [
                    ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22']],
                ],
            ],
        ];
    }

    private function getExpectedStandardFormatWithoutFilter(string $itemIdentifier): array
    {
        return [
            'identifier' => 'foo',
            'values' => [
                'text' => [
                    ['locale' => 'en_US', 'scope' => 'mobile', 'data' => 'a text'],
                    ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'un texte'],
                ],
                'asset_attr1' => [
                    ['locale' => null, 'scope' => null, 'data' => ['assetCode01', 'assetCode02'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr1/file_01.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/bad_locale.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/file_02.jpg',
                        'files/' . $itemIdentifier . '/asset_attr1/bad_scope.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/en_US/file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/fr_FR/file_12.jpg',
                    ]],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14'], 'paths' => [
                        'files/' . $itemIdentifier . '/asset_attr2/de_DE/file_13.jpg',
                    ]],
                ],
                'asset_attr3' => [
                    ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22'], 'paths' => [
                        'files/product1/asset_attr3/ecommerce/file_14.jpg',
                    ]],
                ],
            ],
        ];
    }

    private function buildAttribute(string $code, string $type, string $referenceDataName = ''): Attribute
    {
        return new Attribute(
            $code,
            $type,
            ['reference_data_name' => $referenceDataName],
            false,
            false,
            null,
            null,
            null,
            'backend_type',
            []
        );
    }
}
