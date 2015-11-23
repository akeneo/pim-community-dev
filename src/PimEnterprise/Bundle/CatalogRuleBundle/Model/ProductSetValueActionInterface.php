<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Model;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\FieldImpactActionInterface;

/**
 * Set action used in product rules.
 * A set action value is used to set a product field (or product value) with a given value
 * for a scope and a locale.
 *
 * For example : set description-fr_FR-ecommerce to 'foo'
 * @deprecated will be removed in 1.6 please use
 *             PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductSetValueActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'set_value';

    /**
     * @return string
     */
    public function getField();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return string
     */
    public function getScope();

    /**
     * @param string $field
     *
     * @return ProductSetValueActionInterface
     */
    public function setField($field);

    /**
     * @param mixed $value
     *
     * @return ProductSetValueActionInterface
     */
    public function setValue($value);

    /**
     * @param string $locale
     *
     * @return ProductSetValueActionInterface
     */
    public function setLocale($locale);

    /**
     * @param string $scope
     *
     * @return ProductSetValueActionInterface
     */
    public function setScope($scope);
}
