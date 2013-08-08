<?php

namespace Oro\Bundle\EmailBundle\Provider;

class TwigSecurityPolicy extends \Twig_Sandbox_SecurityPolicy
{
    public function checkSecurity($tags, $filters, $functions)
    {
        try {
            parent::checkSecurity($tags, $filters, $functions);
        } catch (\Twig_Sandbox_SecurityError $e) {
            if (preg_match('#^([\w]+)\s+"([^"]+)"\s+#', $e->getMessage(), $match)) {
                $statementName = strtolower($match[1]);
                $statement = $match[2];

                throw new \Twig_Sandbox_SecurityError('statement='.$statement.'&statementName='.$statementName);
            }
        }
    }

    public function checkMethodAllowed($obj, $method)
    {
        try {
            parent::checkMethodAllowed($obj, $method);
        } catch (\Twig_Sandbox_SecurityError $e) {
            throw new \Twig_Sandbox_SecurityError('class='.get_class($obj).'&method='.$method);
        }
    }

    public function checkPropertyAllowed($obj, $property)
    {
        try {
            parent::checkPropertyAllowed($obj, $property);
        } catch (\Twig_Sandbox_SecurityError $e) {
            throw new \Twig_Sandbox_SecurityError('class='.get_class($obj).'&property='.$property);
        }
    }
}