<?php

namespace Pim\Component\Catalog\Model;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductUniqueData implements ProductUniqueDataInterface
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var ProductValueInterface */
    protected $value;

    /** @var AttributeInterface */
    protected $attribute;

    /** @var mixed */
    protected $rawData;

    /**
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     */
    public function __construct(ProductInterface $product, ProductValueInterface $value)
    {
        $this->product = $product;
        $this->setProductValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductValue(ProductValueInterface $value)
    {
        $this->value = $value;
        $this->attribute = $value->getAttribute();
        $this->rawData = $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ProductUniqueDataInterface $uniqueValue)
    {
        return $this->getAttribute() === $uniqueValue->getAttribute() &&
            $this->getRawData() === $uniqueValue->getRawData();
    }
}
