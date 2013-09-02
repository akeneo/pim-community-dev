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
        return ''; //TODO: Removed after new acl implemented
        /*if (!($targetEntity->reflClass->getName() == "OroCRM\\Bundle\\AccountBundle\\Entity\\Account")) {
            return '';
        }*/

        return $this->builder->buildFilterConstraint($targetEntity->reflClass->getName(), $targetTableAlias);
    }

    /**
     * @param OwnershipSqlFilterBuilder $builder
     */
    public function setBuilder(OwnershipSqlFilterBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Setting current user id parameter to prevent doctrine queries caching with current filter for all users
     */
    public function setUserParameter()
    {
        $this->setParameter('user_id', $this->builder->getUserId());
    }
}
