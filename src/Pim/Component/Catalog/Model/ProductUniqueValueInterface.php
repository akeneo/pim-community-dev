<?php


namespace Pim\Component\Catalog\Model;


interface ProductUniqueValueInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * @return ProductValueInterface
     */
    public function getValue();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return mixed
     */
    public function getRawValue();

    /**
     * @param mixed $rawValue
     */
    public function setRawValue($rawValue);
}
