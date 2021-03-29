<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Abstract metric (backend type entity)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractMetric implements MetricInterface
{
    /** @var float */
    protected $data;

    /** @var string */
    protected $unit;

    /** @var float */
    protected $baseData;

    /** @var string */
    protected $baseUnit;

    /** @var string */
    protected $family;

    /**
     * TODO master: add type hiting for more strict data
     *
     * @param string $family
     * @param string $unit
     * @param float  $data
     * @param string $baseUnit
     * @param float  $baseData
     */
    public function __construct($family, $unit, $data, $baseUnit, $baseData)
    {
        $this->family = $family;
        $this->unit = $unit;
        $this->data = $data;
        $this->baseUnit = $baseUnit;
        $this->baseData = $baseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseData()
    {
        return $this->baseData;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUnit()
    {
        return $this->baseUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(MetricInterface $metric)
    {
        if ($metric->getUnit() !== $this->unit) {
            return false;
        }

        if (!is_string($metric->getData()) || !is_string($this->data)) {
            return $metric->getData() === $this->data;
        }

        return 0 === strcmp(
                preg_replace('/\.0*$/', '', $metric->getData()),
                preg_replace('/\.0*$/', '', $this->data)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return join(' ', array_filter([
            $this->data !== null ? sprintf('%.4F', $this->data) : null,
            $this->unit,
        ]));
    }
}
