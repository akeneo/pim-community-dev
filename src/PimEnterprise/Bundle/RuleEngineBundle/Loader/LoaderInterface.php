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

use PimEnterprise\Bundle\RuleEngineBundle\Entity\RuleInstanceInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Transform an rule instance (an entity) to a business rule
 */
interface LoaderInterface
{
    /**
     * @param RuleInstanceInterface $instance
     *
     * @return RuleInterface
     */
    public function load(RuleInstanceInterface $instance);

    /**
     * @param RuleInstanceInterface $instance
     *
     * @return bool
     */
    public function supports(RuleInstanceInterface $instance);
}
