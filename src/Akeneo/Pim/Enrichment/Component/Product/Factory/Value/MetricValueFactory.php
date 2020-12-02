<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates metric product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricValueFactory extends AbstractValueFactory
{
    /** @var MetricFactory */
    protected $metricFactory;

    public function __construct(
        MetricFactory $metricFactory,
        string $productValueClass,
        string $supportedAttributeType
    ) {
        parent::__construct($productValueClass, $supportedAttributeType);

        $this->metricFactory = $metricFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            $data = [
                'amount' => null,
                'unit'   => $attribute->getDefaultMetricUnit(),
            ];
        }

        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        if (!\array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!\array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                static::class,
                $data
            );
        }

        return $this->metricFactory->createMetric($attribute->getMetricFamily(), $data['unit'], $data['amount']);
    }
}
