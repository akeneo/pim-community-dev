<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;

/**
 * Copy action interface used in product rules.
 * A copy action value is used to copy a product source value to a product target value.
 *
 * For example : description-fr_FR-ecommerce to description-fr_CH-tablet
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ProductCopyActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'copy';

    /**
     * @return string
     */
    public function getFromField();

    /**
     * @return string
     */
    public function getToField();

    /**
     * @return array
     */
    public function getOptions();
}
