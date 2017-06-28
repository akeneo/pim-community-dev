<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\MetricInterface;

/**
 * Product value for "pim_catalog_metric" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricValue extends AbstractValue implements MetricValueInterface
{
    /** @var MetricInterface */
    protected $data;

    /**
     * @param AttributeInterface   $attribute
     * @param string               $channel
     * @param string               $locale
     * @param MetricInterface|null $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, MetricInterface $data = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        if (null === $this->data) {
            return null;
        }

        return $this->data->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit()
    {
        if (null === $this->data) {
            return null;
        }

        return $this->data->getUnit();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (null !== $this->data && (null !== $data = $this->data->getData())) {
            return sprintf('%.4F %s', $data, $this->data->getUnit());
        }

        return '';
    }
}
