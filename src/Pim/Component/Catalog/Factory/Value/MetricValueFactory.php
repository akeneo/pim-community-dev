<?php

namespace Pim\Component\Catalog\Factory\Value;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Factory that creates metric product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricValueFactory implements ValueFactoryInterface
{
    /** @var MetricFactory */
    protected $metricFactory;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param MetricFactory $metricFactory
     * @param string        $productValueClass
     * @param string        $supportedAttributeType
     */
    public function __construct(MetricFactory $metricFactory, $productValueClass, $supportedAttributeType)
    {
        $this->metricFactory = $metricFactory;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [
                'amount' => null,
                'unit'   => $attribute->getDefaultMetricUnit(),
            ];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->metricFactory->createMetric($attribute->getMetricFamily(), $data['unit'], $data['amount'])
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if metric data are valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                static::class,
                $data
            );
        }
    }
}
