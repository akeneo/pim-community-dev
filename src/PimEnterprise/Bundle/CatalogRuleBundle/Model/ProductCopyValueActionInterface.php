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
use PimEnterprise\Component\CatalogRule\Model\FieldImpactActionInterface;

/**
 * Copy action interface used in product rules.
 * A copy action value is used to copy a product source value to a product target value.
 *
 * For example : description-fr_FR-ecommerce to description-fr_CH-tablet
 * @deprecated will be removed in 1.6 please use
 *             PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductCopyValueActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'copy_value';

    /**
     * @return string
     */
    public function getFromField();

    /**
     * @return mixed
     */
    public function getFromLocale();

    /**
     * @return string
     */
    public function getFromScope();

    /**
     * @return string
     */
    public function getToField();

    /**
     * @return mixed
     */
    public function getToLocale();

    /**
     * @return string
     */
    public function getToScope();

    /**
     * @param string $fromField
     *
     * @return ProductCopyValueActionInterface
     */
    public function setFromField($fromField);

    /**
     * @param mixed $fromLocale
     *
     * @return ProductCopyValueActionInterface
     */
    public function setFromLocale($fromLocale);

    /**
     * @param string $fromScope
     *
     * @return ProductCopyValueActionInterface
     */
    public function setFromScope($fromScope);

    /**
     * @param string $toField
     *
     * @return ProductCopyValueActionInterface
     */
    public function setToField($toField);

    /**
     * @param mixed $toLocale
     *
     * @return ProductCopyValueActionInterface
     */
    public function setToLocale($toLocale);

    /**
     * @param string $toScope
     *
     * @return ProductCopyValueActionInterface
     */
    public function setToScope($toScope);
}
