<?php
require_once 'PHP/PMD/AbstractRule.php';
require_once 'PHP/PMD/Rule/IClassAware.php';
require_once 'PHP/PMD/Rule/IMethodAware.php';
require_once 'PHP/PMD/Rule/IFunctionAware.php';

/**
 * This rule class will detect variables, parameters and properties with short
 * names, but will not check variables name that are part of the excludedVariable
 * property
 *
 */
class Akeneo_PMD_Rule_Naming_ShortVariableWithExclusion
       extends PHP_PMD_Rule_Naming_ShortVariable
{

    /**
     * {@inheritdoc}
     */
    protected function checkNodeImage(PHP_PMD_AbstractNode $node)
    {
        $excludedVariables = explode('|', $this->getStringProperty('excludeVariables'));
        if (!in_array($node->getImage(), $excludedVariables)) {
            parent::checkNodeImage($node);
        }
    }

}
