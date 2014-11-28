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
 * Decors a rule to be apply to select its subjects and to be able to apply it.
 * It represents a rule that has been loaded from the database
 * by a \PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface LoadedRuleInterface extends RuleInterface
{
    /**
     * @return ConditionInterface[]
     */
    public function getConditions();

    /**
     * @param ConditionInterface[] $conditions
     *
     * @return LoadedRuleInterface
     */
    public function setConditions(array $conditions);

    /**
     * @param ConditionInterface $condition
     *
     * @return LoadedRuleInterface
     */
    public function addCondition(ConditionInterface $condition);

    /**
     * @return ActionInterface[]
     */
    public function getActions();

    /**
     * @param ActionInterface[] $actions
     *
     * @return LoadedRuleInterface
     */
    public function setActions(array $actions);

    /**
     * @param ActionInterface $action
     *
     * @return LoadedRuleInterface
     */
    public function addAction(ActionInterface $action);
}
