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

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;

/**
 * Link between a rule and a resource to know that the rule if applicable to the resource.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleRelation implements RuleRelationInterface
{
    /** @var int */
    protected $id;

    /** @var RuleDefinitionInterface */
    protected $rule;

    /** @var string */
    protected $resourceName;

    /** @var mixed */
    protected $resourceId;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleDefinition()
    {
        return $this->rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleDefinition(RuleDefinitionInterface $rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }
}
