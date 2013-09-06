<?php
namespace Oro\Bundle\SecurityBundle\ORM\SqlFilter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class OwnershipFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        return '';
    }
}