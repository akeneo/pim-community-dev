<?php
namespace Oro\Bundle\SecurityBundle\ORM\SqlFilter;

use Oro\Bundle\SecurityBundle\ORM\OwnershipSqlFilterBuilder;
use Doctrine\Common\Util\ClassUtils;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;

class OwnershipFilter extends SQLFilter
{
    /**
     * @var OwnershipSqlFilterBuilder
     */
    protected $builder;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!($targetEntity->reflClass->getName() == "Oro\\Bundle\\UserBundle\\Entity\\User")) {
            return '';
        }

        return $this->builder->buildFilterConstraint($targetEntity->reflClass->getName(), $targetTableAlias);
    }

    public function setBuilder(OwnershipSqlFilterBuilder $builder)
    {
        $this->builder = $builder;
    }
}
