<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface EntityWithValuesBuilderInterface
{
    /**
     * Creates required value(s) to add the attribute to entity
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute
     */
    public function addAttribute(EntityWithValuesInterface $entityWithValues, AttributeInterface $attribute);

    /**
     * Add or replace a value to an entity
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute
     * @param string                    $locale
     * @param string                    $scope
     * @param mixed                     $data
     *
     * @return EntityWithValuesInterface
     */
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    );
}
