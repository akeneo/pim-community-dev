<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector\AttributeSelectorRegistry;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector\PropertySelectorRegistry;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductExportProcessorSpec extends ObjectBehavior
{
    function let(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AttributeSelectorRegistry $attributeSelectorRegistry,
        PropertySelectorRegistry $propertySelectorRegistry,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith(
            $attributeSelectorRegistry,
            $propertySelectorRegistry,
            $getAttributes
        );
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
    }

    public function it_processes_product(
        ProductInterface $product,
        JobParameters $jobParameters,
        ValueInterface $nameValue,
        GetAttributes $getAttributes,
        AttributeSelectorRegistry $attributeSelectorRegistry,
        PropertySelectorRegistry $propertySelectorRegistry
    ) {
        $name = $this->createAttribute('name');
        $jobParameters->get('columns')->willReturn([
            [
                'target' => 'categories-export',
                'sources' => [
                    [
                        'type' => 'property',
                        'code' => 'categories',
                        'locale' => null,
                        'channel' => null,
                        'selection' => [
                            'type' => 'code'
                        ],
                    ],
                ],
            ],
            [
                'target' => 'name-export',
                'sources' => [
                    [
                        'type' => 'attribute',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'selection' => [
                            'type' => 'code',
                        ],
                    ],
                ],
            ],
        ]);

        $product->getValue('name', null, null)->willReturn($nameValue);
        $getAttributes->forCode('name')->willReturn($name);
        $attributeSelectorRegistry
            ->applyAttributeSelection(['type' => 'code'], $name, $nameValue)
            ->willReturn('name value');

        $propertySelectorRegistry
            ->applyPropertySelection(['type' => 'code'], $product, 'categories')
            ->willReturn('my category');

        $this->process($product)->shouldReturn(
            [
                'categories-export' => 'my category',
                'name-export' => 'name value',
            ]
        );
    }

    private function createAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            null,
            null,
            'text',
            []
        );
    }
}
