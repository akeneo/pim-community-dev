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

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Link between a rule and a resource to know that the rule if applicable to the resource.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleRelationInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return RuleDefinitionInterface
     */
    public function getRuleDefinition();

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
     * @return RuleRelationInterface
     */
    public function setRuleDefinition(RuleDefinitionInterface $rule);

    /**
     * @param string $resourceName
     *
     * @return RuleRelationInterface
     */
    public function setResourceName($resourceName);

    /**
     * @param mixed $resourceId
     *
     * @return RuleRelationInterface
     */
    public function setResourceId($resourceId);
}
