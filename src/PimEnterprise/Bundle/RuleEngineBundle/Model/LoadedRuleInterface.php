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
interface LoadedRuleInterface extends RuleInterface
{
    /**
     * @return array
     */
    public function getConditions();

    /**
     * @param array $conditions
     *
     * @return LoadedRuleInterface
     */
    public function setConditions(array $conditions);

    /**
     * @param array $condition
     *
     * @return LoadedRuleInterface
     */
    public function addCondition(array $condition);

    /**
     * @return array
     */
    public function getActions();

    /**
     * @param array $actions
     *
     * @return LoadedRuleInterface
     */
    public function setActions(array $actions);

    /**
     * @param array $action
     *
     * @return LoadedRuleInterface
     */
    public function addAction(array $action);
}
