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
 * Decores a rule to be apply to select its subjetcs and to be able to apply it.
 * It represents a rule that has been loaded from the database
 * by a \PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface LoadedRuleDecoratorInterface extends RuleInterface
{
    /**
     * @return array
     */
    public function getConditions();

    /**
     * @param array $conditions
     *
     * @return LoadedRuleDecoratorInterface
     */
    public function setConditions(array $conditions);

    /**
     * @param array $condition
     *
     * @return LoadedRuleDecoratorInterface
     */
    public function addCondition(array $condition);

    /**
     * @return array
     */
    public function getActions();

    /**
     * @param array $actions
     *
     * @return LoadedRuleDecoratorInterface
     */
    public function setActions(array $actions);

    /**
     * @param array $action
     *
     * @return LoadedRuleDecoratorInterface
     */
    public function addAction(array $action);
}
