<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Abstract price backend type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductPrice implements ProductPriceInterface
{
    /** @var float */
    protected $data;

    /** @var null */
    protected $currency;

    /**
     * Constructor
     *
     * @param float  $data
     * @param string $currency
     */
    public function __construct($data, $currency)
    {
        $this->data = $data;
        $this->currency = $currency;
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
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ProductPriceInterface $price)
    {
        return $price->getData() === $this->data && $price->getCurrency() === $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return ($this->data !== null) ? sprintf('%.2F %s', $this->data, $this->currency) : '';
    }
}
