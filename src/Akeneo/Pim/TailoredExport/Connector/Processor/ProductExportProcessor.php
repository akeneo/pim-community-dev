<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TailoredExport\Connector\Processor\Operation\OperationHandler;
use Akeneo\Pim\TailoredExport\Connector\Processor\ValueSelector\ValueSelectorRegistry;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class ProductExportProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private ?StepExecution $stepExecution = null;
    private OperationHandler $operationHandler;
    private GetAttributes $getAttributes;
    private ValueSelectorRegistry $valueSelectorRegistry;

    public function __construct(
        OperationHandler $operationHandler,
        GetAttributes $getAttributes,
        ValueSelectorRegistry $valueSelectorRegistry
    ) {
        $this->operationHandler = $operationHandler;
        $this->getAttributes = $getAttributes;
        $this->valueSelectorRegistry = $valueSelectorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \Exception('Invalid argument');
        }

        $productStandard = [];

        // $columns = $this->stepExecution->getJobParameters()->get('columns') ?? [];

        $columns =         [
            [
                'target' => 'name',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'foo',
                            ],
                            [
                                'type' => 'replace',
                                'mapping' => [
                                    'Bag' => 'sac a pied',
                                ],
                            ],
                        ],
                    ],
                    [
                        'uuid' => '0002',
                        'code' => 'name',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'toto',
                            ],
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                        [
                            'type' => 'source',
                            'value' => '0002',
                        ],
                        [
                            'type' => 'string',
                            'value' => 'cm',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'collecgion',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'collection',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'replace',
                                'mapping' => [
                                    'spring_2015' => 'printemps 2015 yeaaah',
                                    'summer_2017' => 'ete 2020'
                                ],
                            ],
                        ],
                        'selection' => [
                            'type' => 'label',
                            'locale' => 'fr_FR'
                        ]
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'default_value',
                                'value' => 'toto',
                            ],
                            [
                                'type' => 'convert',
                                'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'amount', // unit_code, unit_label, amount
                        ],
                    ],
                    [
                        'uuid' => '0002',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'convert',
                                'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'unit_label',
                            'locale' => 'fr_FR'
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                        [
                            'type' => 'string',
                            'value' => ' ',
                        ],
                        [
                            'type' => 'source',
                            'value' => '0002',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight-customized',
                'sources' => [
                    [
                        'uuid' => '0001',
                        'code' => 'weight',
                        'locale' => null,
                        'channel' => null,
                        'operations' => [
                            [
                                'type' => 'convert',
                                'unit' => 'MILLIGRAM',
                            ],
                        ],
                        'selection' => [
                            'type' => 'amount',
                        ],
                    ],
                ],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'source',
                            'value' => '0001',
                        ],
                    ],
                ],
            ],
            [
                'target' => 'weight-unit-customized',
                'sources' => [],
                'format' => [
                    'type' => 'concat',
                    'elements' => [
                        [
                            'type' => 'string',
                            'value' => 'MILLIGRAM',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($columns as $column) {
            $operationSourceValues = [];

            foreach ($column['sources'] as $source) {
                $value = $product->getValue($source['code'], $source['locale'], $source['channel']);

                $attribute = $this->getAttributes->forCode($source['code']);

                //TODO $attribute can be null
                $operationSourceValues[$source['uuid']] = $this->applyOperations($source['operations'], $attribute, $value);
                if (isset($source['selection'])) {
                    $operationSourceValues[$source['uuid']] = $this->applySelection($source['selection'], $attribute, $operationSourceValues[$source['uuid']]);
                }
            }

            $value = $this->applyFormat($column['format'], $operationSourceValues);

            $productStandard[$column['target']] = (string) $value;
        }

        return $productStandard;
    }

    private function applyOperations(array $operations, Attribute $attribute, ?ValueInterface $value)
    {
        return $this->operationHandler->handleOperations($operations, $attribute, $value);
    }

    private function applySelection(array $selection, Attribute $attribute, $value): string
    {
        return $this->valueSelectorRegistry->applySelection($selection, $attribute, $value);
    }

    private function applyFormat($format, $operationSourceValues)
    {
        $value = '';
        if ('concat' === $format['type']) {
            foreach ($format['elements'] as $element) {
                if ('source' === $element['type']) {
                    $value .= $operationSourceValues[$element['value']];
                } else {
                    $value .= $element['value'];
                }
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
