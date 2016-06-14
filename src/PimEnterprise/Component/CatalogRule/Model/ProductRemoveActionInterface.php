<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Model;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;

/**
 * Remove action interface used in product rules.
 * A remove action is used to remove an array of items from a product.
 *
 * For example : remove ['socks'] from categories
 * or          : remove ['red', 'green'] from colors
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
interface ProductRemoveActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'remove';

    /**
     * @return string
     */
    public function getField();

    /**
     * @return array
     */
    public function getItems();

    /**
     * @return array
     */
    public function getOptions();
}
