<?php


namespace Pim\Component\Catalog\Model;


class ProductUniqueValue implements ProductUniqueValueInterface
{
    private $id;

    /** @var ProductInterface */
    private $product;

    /** @var ProductValueInterface */
    private $value;

    /** @var AttributeInterface */
    private $attribute;

    /** @var mixed */
    private $rawValue;

    public function __construct(ProductInterface $product, ProductValueInterface $value)
    {
        $this->product = $product;
        $this->value = $value;
        $this->attribute = $this->value->getAttribute();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getValue()
    {
        return $this->getValue();
    }

    public function getData()
    {
        return $this->value->getData();
    }

    public function getRawValue()
    {
        return $this->rawValue;
    }

    public function setRawValue($rawValue)
    {
        $this->rawValue = $rawValue;
    }
}
