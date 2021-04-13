<?php


namespace Akeneo\Pim\TailoredExport\Connector\Processor\Operation;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\MetricValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

class MetricConverterOperationHandler implements OperationHandlerInterface
{
    private MeasureConverter $measureConverter;
    private MetricValueFactory $metricValueFactory;

    public function __construct(MeasureConverter $measureConverter, MetricValueFactory $metricValueFactory)
    {
        $this->measureConverter = $measureConverter;
        $this->metricValueFactory = $metricValueFactory;
    }

    public function handleOperation(array $operation, Attribute $attribute, $value)
    {
        $this->measureConverter->setFamily($attribute->metricFamily());

        $amountConverted = $this->measureConverter->convert($value->getUnit(), $operation['unit'], $value->getAmount());

        return $this->metricValueFactory->createByCheckingData(
            $attribute,
            $value->getScopeCode(),
            $value->getLocaleCode(),
            [
                'amount' => $amountConverted,
                'unit' => $operation['unit']
            ]
        );
    }

    public function supports(array $operation, Attribute $attribute, $value): bool
    {
        return
            'convert' === $operation['type']
            && $value instanceof MetricValueInterface;
    }
}
