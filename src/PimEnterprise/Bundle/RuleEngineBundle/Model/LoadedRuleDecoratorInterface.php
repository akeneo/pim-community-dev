<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Model;

/**
 * RuleInterface decorator to represent a rule that has been loaded from the database
 * by a \PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface
 *
 *
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
}
