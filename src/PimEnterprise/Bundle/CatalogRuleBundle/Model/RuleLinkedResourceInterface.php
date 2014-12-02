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

use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Link between a rule and a resource to know that the rule if applicable to the resource.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleLinkedResourceInterface
{
    /**
     * @return RuleDefinitionInterface
     */
    public function getRule();

    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @return string
     */
    public function getResourceId();

    /**
     * @param RuleDefinitionInterface $rule
     *
     * @return RuleLinkedResourceInterface
     */
    public function setRule(RuleDefinitionInterface $rule);

    /**
     * @param string $resourceName
     *
     * @return RuleLinkedResourceInterface
     */
    public function setResourceName($resourceName);

    /**
     * @param mixed $resourceId
     *
     * @return RuleLinkedResourceInterface
     */
    public function setResourceId($resourceId);
}
