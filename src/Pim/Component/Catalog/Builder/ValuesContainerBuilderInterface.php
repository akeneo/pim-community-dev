<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;

interface ValuesContainerBuilderInterface
{
    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ValuesContainerInterface $values
     * @param AttributeInterface       $attribute
     */
    public function addAttribute(ValuesContainerInterface $values, AttributeInterface $attribute);

    /**
     * Add or replace a product value.
     *
     * @param ValuesContainerInterface $values
     * @param AttributeInterface       $attribute
     * @param string                   $locale
     * @param string                   $scope
     * @param mixed                    $data
     *
     * @return ValuesContainerInterface
     */
    public function addOrReplaceValue(
        ValuesContainerInterface $values,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    );
}
