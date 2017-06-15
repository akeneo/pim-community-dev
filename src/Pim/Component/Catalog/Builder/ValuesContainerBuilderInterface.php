<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
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
