<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\AST\RangeVariableDeclaration;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\AST\ConditionalPrimary;
use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclConditionStorage;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\SubRequestAclConditionStorage;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAssociationCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AccessDeniedCondition;

/**
 * Class ACLHelper
 * This class analyse input query for acl and mark it with ORO_ACL_WALKER if it need to be ACL protected.
 */
class AclHelper
{
    const ORO_ACL_WALKER = 'Oro\Bundle\SecurityBundle\ORM\Walker\AclWalker';

    /**
     * @var OwnershipConditionDataBuilder
     */
    protected $builder;

    /**
     * @var EntityManager
     */
    protected $em;

    protected $entityAliases;

    /**
     * @param $builder
     */
    public function __construct(OwnershipConditionDataBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Mark query as acl protected
     *
     * @param Query|QueryBuilder $query
     * @param string $permission
     *
     * @return Query
     */
    public function apply($query, $permission = "VIEW")
    {
        $this->entityAliases = [];
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        /** @var Query $query */
        $this->em = $query->getEntityManager();

        $ast = $query->getAST();
        if ($ast instanceof SelectStatement) {
            list ($whereConditions, $joinConditions) = $this->processSelect($ast, $permission, $query);
            $conditionStorage = new AclConditionStorage($whereConditions, $joinConditions);
            if ($ast->whereClause) {
                $this->processSubselects($ast, $conditionStorage, $permission, $query);
            }

            // We have access level check conditions. So mark query for acl walker.
            if (!$conditionStorage->isEmpty()) {
                $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS,
                    array_merge(
                        $query->getHints(),
                        array(self::ORO_ACL_WALKER)
                    )
                );
                $query->setHint(AclWalker::ORO_ACL_CONDITION, $conditionStorage);
            }
        }

        return $query;
    }

    /**
     * Check subrequests for acl access level
     *
     * @param SelectStatement $ast
     * @param AclConditionStorage $storage
     * @param $permission
     */
    protected function processSubselects(SelectStatement $ast, AclConditionStorage $storage, $permission)
    {
        $conditionalExpression = $ast->whereClause->conditionalExpression;
        if ($conditionalExpression instanceof ConditionalPrimary) {
            // we have request with only one where condition
            $expression = $conditionalExpression->simpleConditionalExpression;
            if (isset($expression->subselect)
                && $expression->subselect instanceof Subselect
            ) {
                $subRequestAclStorage = $this->processSubselect($expression->subselect, $permission);
                if (!$subRequestAclStorage->isEmpty()) {
                    $subRequestAclStorage->setFactorId(0);
                    $storage->addSubRequests($subRequestAclStorage);
                }
            }
        } else {
            // we have request with only many where conditions
            $subQueryAcl = [];
            foreach ($conditionalExpression->conditionalFactors as $factorId => $expression) {
                if (isset($expression->simpleConditionalExpression->subselect)
                    && $expression->simpleConditionalExpression->subselect instanceof Subselect
                ) {
                    $subRequestAclStorage = $this->processSubselect(
                        $expression->simpleConditionalExpression->subselect,
                        $permission
                    );
                    if (!$subRequestAclStorage->isEmpty()) {
                        $subRequestAclStorage->setFactorId($factorId);
                        $subQueryAcl[] = $subRequestAclStorage;
                    }
                }
            }
            if (!empty($subQueryAcl)) {
                $storage->setSubRequests($subQueryAcl);
            }
        }
    }

    /**
     * Check Access levels for subrequest
     *
     * @param Subselect $subSelect
     * @param $permission
     * @return SubRequestAclConditionStorage
     */
    protected function processSubselect(Subselect $subSelect, $permission)
    {
        list ($whereConditions, $joinConditions) = $this->processSelect($subSelect, $permission);

        return new SubRequestAclConditionStorage($whereConditions, $joinConditions);
    }

    /**
     * Check request
     *
     * @param Subselect|SelectStatement $select
     * @param string $permission
     * @return array
     */
    protected function processSelect($select, $permission)
    {
        if ($select instanceof SelectStatement) {
            $isSubRequest = false;
        } else {
            $isSubRequest = true;
        }
        $whereConditions = [];
        $joinConditions = [];
        $fromClause = $isSubRequest ? $select->subselectFromClause : $select->fromClause;

        foreach ($fromClause->identificationVariableDeclarations as $fromKey => $identificationVariableDeclaration) {
            $condition = $this->processRangeVariableDeclaration(
                $identificationVariableDeclaration->rangeVariableDeclaration,
                $permission
            );
            if ($condition) {
                $whereConditions[] = $condition;
            }

            // check joins
            if (!empty($identificationVariableDeclaration->joins)) {
                /** @var $join Join */
                foreach ($identificationVariableDeclaration->joins as $joinKey => $join) {
                    //check if join is simple join (join some_table on (some_table.id = parent_table.id))
                    if ($join->joinAssociationDeclaration instanceof RangeVariableDeclaration) {
                        $condition = $this->processRangeVariableDeclaration(
                            $join->joinAssociationDeclaration,
                            $permission,
                            true
                        );
                    } else {
                        $condition = $this->processJoinAssociationPathExpression(
                            $identificationVariableDeclaration, $joinKey, $permission
                        );
                    }
                    if ($condition) {
                        $condition->setFromKey($fromKey);
                        $condition->setJoinKey($joinKey);
                        $joinConditions[] = $condition;
                    }
                }
            }
        }

        return array($whereConditions, $joinConditions);
    }

    /**
     * Process Joins without "on" statement
     *
     * @param IdentificationVariableDeclaration $declaration
     * @param $key
     * @param $permission
     * @return JoinAssociationCondition
     */
    protected function processJoinAssociationPathExpression(IdentificationVariableDeclaration $declaration, $key, $permission)
    {
        /** @var Join $join */
        $join = $declaration->joins[$key];

        $joinParentEntityAlias = $join->joinAssociationDeclaration->joinAssociationPathExpression->identificationVariable;
        $joinParentClass = $this->entityAliases[$joinParentEntityAlias];
        $metadata = $this->em->getClassMetadata($joinParentClass);

        $fieldName = $join->joinAssociationDeclaration->joinAssociationPathExpression->associationField;

        $associationMapping = $metadata->getAssociationMapping($fieldName);
        $targetEntity = $associationMapping['targetEntity'];

        if (!isset($this->entityAliases[$join->joinAssociationDeclaration->aliasIdentificationVariable])) {
            $this->entityAliases[$join->joinAssociationDeclaration->aliasIdentificationVariable] = $targetEntity;
        }

        $resultData = $this->builder->getAclConditionData($targetEntity, $permission);

        if ($resultData && is_array($resultData)) {
            $entityField = $value = null;
            if (!empty($resultData)) {
                list($entityField, $value) = $resultData;
            }

            return new JoinAssociationCondition(
                $join->joinAssociationDeclaration->aliasIdentificationVariable,
                $entityField,
                $value,
                $targetEntity,
                isset($associationMapping['joinColumns']) ? $associationMapping['joinColumns'] : $associationMapping['mappedBy']
            );
        }
    }

    /**
     * Process where statement
     *
     * @param RangeVariableDeclaration $rangeVariableDeclaration
     * @param $permission
     * @param bool $isJoin
     * @return null|AclCondition|JoinAclCondition
     */
    protected function processRangeVariableDeclaration(
        RangeVariableDeclaration $rangeVariableDeclaration,
        $permission,
        $isJoin = false
    ) {
        $this->addEntityAlias($rangeVariableDeclaration);
        $entityName = $rangeVariableDeclaration->abstractSchemaName;
        $entityAlias = $rangeVariableDeclaration->aliasIdentificationVariable;

        $resultData = $this->builder->getAclConditionData($entityName, $permission);

        if ($resultData === null || !empty($resultData)) {
            $entityField = $value = null;
            if (!empty($resultData)) {
                list($entityField, $value) = $resultData;
            }
            if ($isJoin) {
                return new JoinAclCondition(
                    $entityAlias, $entityField, $value
                );
            } else {
                return new AclCondition(
                    $entityAlias, $entityField, $value
                );
            }
        }

        return null;
    }

    protected function addEntityAlias(RangeVariableDeclaration $rangeDeclaration)
    {
        $alias = $rangeDeclaration->aliasIdentificationVariable;
        if (!isset($this->entityAliases[$alias])) {
            $this->entityAliases[$alias] = $rangeDeclaration->abstractSchemaName;
        }
    }
}
