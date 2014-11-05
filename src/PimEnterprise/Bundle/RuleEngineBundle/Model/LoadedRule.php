<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Model;

/**
 * Decores a rule to be apply to select its subjects and to be able to apply it.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class LoadedRule implements LoadedRuleInterface
{
    /** @var RuleInterface */
    protected $rule;

    /** @var array */
    protected $conditions;

    /** @var array */
    protected $actions;

    /**
     * The constructor
     *
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
        $this->actions = [];
        $this->conditions = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(array $condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAction(array $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->rule->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->rule->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->rule->setCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->rule->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->rule->setType($type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->rule->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->rule->setContent($content);

        return $this;
    }
}
