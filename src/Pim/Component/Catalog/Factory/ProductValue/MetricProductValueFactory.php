<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Factory\MetricFactory;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Factory that creates metric product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricProductValueFactory implements ProductValueFactoryInterface
{
    /** @var MetricFactory */
    protected $metricFactory;

    /** @var string */
    protected $metricProductValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param MetricFactory $metricFactory
     * @param string        $metricProductValueClass
     * @param string        $supportedAttributeType
     */
    public function __construct(MetricFactory $metricFactory, $metricProductValueClass, $supportedAttributeType)
    {
        if (!class_exists($metricProductValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $metricProductValueClass)
            );
        }

        $this->metricFactory = $metricFactory;
        $this->metricProductValueClass = $metricProductValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        $value = new $this->metricProductValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        if (null !== $data) {
            $value->setMetric(
                $this->metricFactory->createMetric($attribute->getMetricFamily(), $data['unit'], $data['amount'])
            );
        }

        return $value;
    }

    /**
     * @inheritdoc
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
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'metric',
                'factory',
                gettype($data)
            );
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                'metric',
                'factory',
                implode(', ', array_keys($data))
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                'metric',
                'factory',
                implode(', ', array_keys($data))
            );
        }
    }
}
