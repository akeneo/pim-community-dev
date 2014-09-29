<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Model;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

interface ProductRuleInterface extends RuleInterface
{
    public function getExpression();
    public function setExpression($expression);
}
