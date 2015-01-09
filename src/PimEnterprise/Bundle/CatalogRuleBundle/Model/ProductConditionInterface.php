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

use Akeneo\Bundle\RuleEngineBundle\Model\ConditionInterface;

/**
 * Condition interface used in product rules.
 * A product condition is used to select products given a product field (or a product value), for an operator
 * a value criteria.
 *
 * For example: SKU CONTAINS '%foo%'
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductConditionInterface extends ConditionInterface
{
    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getOperator();

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
     * @return ProductConditionInterface
     */
    public function setField($field);

    /**
     * @param string $operator
     *
     * @return ProductConditionInterface
     */
    public function setOperator($operator);

    /**
     * @param mixed $value
     *
     * @return ProductConditionInterface
     */
    public function setValue($value);

    /**
     * @param string $locale
     *
     * @return ProductConditionInterface
     */
    public function setLocale($locale);

    /**
     * @param string $scope
     *
     * @return ProductConditionInterface
     */
    public function setScope($scope);
}
