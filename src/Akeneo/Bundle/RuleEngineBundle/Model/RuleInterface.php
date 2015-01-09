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
 * It represents a rule that has been loaded from the database
 * by a \Akeneo\Bundle\RuleEngineBundle\Engine\LoaderInterface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleInterface extends RuleDefinitionInterface
{
    /**
     * @return ConditionInterface[]
     */
    public function getConditions();

    /**
     * @param ConditionInterface[] $conditions
     *
     * @return RuleInterface
     */
    public function setConditions(array $conditions);

    /**
     * @param ConditionInterface $condition
     *
     * @return RuleInterface
     */
    public function addCondition(ConditionInterface $condition);

    /**
     * @return ActionInterface[]
     */
    public function getActions();

    /**
     * @param ActionInterface[] $actions
     *
     * @return RuleInterface
     */
    public function setActions(array $actions);

    /**
     * @param ActionInterface $action
     *
     * @return RuleInterface
     */
    public function addAction(ActionInterface $action);
}
