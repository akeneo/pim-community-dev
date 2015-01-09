<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Model;

/**
 * Decores a rule definition to be able to select its subjects and apply it.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class Rule implements RuleInterface
{
    /** @var RuleDefinitionInterface */
    protected $definition;

    /** @var ConditionInterface[] */
    protected $conditions;

    /** @var ActionInterface[] */
    protected $actions;

    /**
     * The constructor
     *
     * @param RuleDefinitionInterface $definition
     */
    public function __construct(RuleDefinitionInterface $definition)
    {
        $this->definition = $definition;
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
    public function addCondition(ConditionInterface $condition)
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
    public function addAction(ActionInterface $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->definition->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->definition->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->definition->setCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->definition->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->definition->setType($type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->definition->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent(array $content)
    {
        $this->definition->setContent($content);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->definition->getPriority();
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        $this->definition->setPriority($priority);

        return $this;
    }
}
