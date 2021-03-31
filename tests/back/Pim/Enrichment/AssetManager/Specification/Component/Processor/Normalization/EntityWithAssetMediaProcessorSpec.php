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
         GetAttributes $getAttributes
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
             $getAttributes
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
         GetAssetMainMediaValuesInterface $getAssetMainMediaValues
     ) {
         $this->givenAJobExecutionWihMediaAndExportFilters();
         $product = new Product();
         $product->setIdentifier('product1');

         $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
         $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

         $result = $this->process($product);
         $result->shouldBe($this->getExpectedStandardFormatWithExportFilters());
     }

    function it_processes_a_product_with_media_and_quick_export_filters(
        ItemProcessorInterface $decoratedItemProcessor,
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ) {
        $this->givenAJobExecutionWihMediaAndQuickExportFilters();
        $product = new Product();
        $product->setIdentifier('product1');

        $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
        $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

        $result = $this->process($product);
        $result->shouldBe($this->getExpectedStandardFormatWithQuickExportFilters());
    }

     function it_processes_a_product_with_media_without_filters(
         ItemProcessorInterface $decoratedItemProcessor,
         GetAssetMainMediaValuesInterface $getAssetMainMediaValues
     ) {
         $this->givenAJobExecutionWihMediaAndWithoutFilters();
         $product = new Product();
         $product->setIdentifier('product1');

         $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $product);
         $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

         $result = $this->process($product);
         $result->shouldBe($this->getExpectedStandardFormatWithoutFilter());
     }

    function it_processes_a_product_model_with_media(
        ItemProcessorInterface $decoratedItemProcessor,
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ) {
        $this->givenAJobExecutionWihMediaAndExportFilters();
        $productModel = new ProductModel();
        $productModel->setCode('product_model1');

        $this->givenAStandardFormatReturnedByDecoratedProcessor($decoratedItemProcessor, $productModel);
        $this->givenAssetMainValuesReturnsSomeValues($getAssetMainMediaValues);

        $result = $this->process($productModel);
        $result->shouldBe($this->getExpectedStandardFormatWithExportFilters());
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
                        'data' => ['filePath' => 'a/b/ab_file_01.jpg', 'originalFilename' => 'file_01.jpg'],
                    ],
                    [
                        'locale' => 'de_DE',
                        'channel' => null,
                        'data' => ['filePath' => 'c/d/cd_bad_locale.jpg', 'originalFilename' => 'bad_locale.jpg'],
                    ],
                ],
                'assetCode02' => [
                    [
                        'locale' => null,
                        'channel' => 'mobile',
                        'data' => ['filePath' => 'e/f/ef_file_02.jpg', 'originalFilename' => 'file_02.jpg'],
                    ],
                    [
                        'locale' => null,
                        'channel' => 'ecommerce',
                        'data' => ['filePath' => '1/2/12_bad_scope.jpg', 'originalFilename' => 'bad_scope.jpg'],
                    ],
                ],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family2', ['assetCode11', 'assetCode12'])
            ->willReturn([
                'assetCode11' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => '3/4/34_file_11.jpg', 'originalFilename' => 'file_11.jpg'],
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
                        'data' => ['filePath' => '5/6/56_file_12.jpg', 'originalFilename' => 'file_12.jpg'],
                    ],
                ],
            ]);

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family2', ['assetCode14'])
            ->willReturn([
                'assetCode14' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => ['filePath' => '7/8/78_file_14.jpg', 'originalFilename' => 'file_14.jpg'],
                    ],
                ],
            ]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family3', ['assetCode21'])->willReturn([]);
        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes('asset_family3', ['assetCode22'])
            ->willReturn([
                'assetCode22' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => 'https://my-server/my-image.jpg',
                    ],
                ],
            ]);
    }

    private function getExpectedStandardFormatWithExportFilters(): array
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
                        'a/b/ab_file_01.jpg',
                        'e/f/ef_file_02.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        '3/4/34_file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        '5/6/56_file_12.jpg',
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

    private function getExpectedStandardFormatWithQuickExportFilters(): array
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
                        'a/b/ab_file_01.jpg',
                        'c/d/cd_bad_locale.jpg',
                        'e/f/ef_file_02.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        '3/4/34_file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        '5/6/56_file_12.jpg',
                    ]],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14'], 'paths' => [
                        '7/8/78_file_14.jpg',
                    ]],
                ],
                'asset_attr3' => [
                    ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22']],
                ],
            ],
        ];
    }

    private function getExpectedStandardFormatWithoutFilter(): array
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
                        'a/b/ab_file_01.jpg',
                        'c/d/cd_bad_locale.jpg',
                        'e/f/ef_file_02.jpg',
                        '1/2/12_bad_scope.jpg',
                    ]],
                ],
                'asset_attr2' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['assetCode11', 'assetCode12'], 'paths' => [
                        '3/4/34_file_11.jpg',
                    ]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['assetCode13'], 'paths' => [
                        '5/6/56_file_12.jpg',
                    ]],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['assetCode14'], 'paths' => [
                        '7/8/78_file_14.jpg',
                    ]],
                ],
                'asset_attr3' => [
                    ['locale' => null, 'scope' => 'mobile', 'data' => ['assetCode21']],
                    ['locale' => null, 'scope' => 'ecommerce', 'data' => ['assetCode22'], 'paths' => [
                        'https://my-server/my-image.jpg',
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
