<?php
namespace Oro\Bundle\EntityBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class Filter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        //var_dump($targetEntity->reflClass);
        if (!$targetEntity->reflClass->implementsInterface('Symfony\\Component\\Security\\Core\\User\\AdvancedUserInterface')) {
            return "";
        }

        return $targetTableAlias . '.id = 24';
    }
}