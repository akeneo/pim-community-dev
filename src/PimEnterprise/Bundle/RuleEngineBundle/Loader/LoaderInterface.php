<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Loader;

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RunnableRuleInterface;

/**
 * Transform an rule instance (an entity) to a business rule
 */
interface LoaderInterface
{
    /**
     * @param RuleInterface $instance


*
*@return RunnableRuleInterface
     */
    public function load(RuleInterface $instance);

    /**
     * @param RuleInterface $instance

     *
*@return bool
     */
    public function supports(RuleInterface $instance);
}
