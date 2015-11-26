<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Model;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use PimEnterprise\Component\CatalogRule\Model\FieldImpactActionInterface;

/**
 * Add action interface used in product rules.
 * An add action is used to add an array of items to an other array of items.
 *
 * For example : add ['socks'] to categories
 * or          : add ['red', 'green'] to colors
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface ProductAddActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'add';

    /**
     * @return string
     */
    public function getField();

    /**
     * @return array
     */
    public function getItems();

    /**
     * @return mixed
     */
    public function getOptions();
}
