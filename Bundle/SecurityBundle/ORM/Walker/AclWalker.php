<?php
namespace Oro\Bundle\SecurityBundle\ORM\Walker;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\TreeWalkerAdapter;

use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\ConditionalPrimary;
use Doctrine\ORM\Query\AST\ArithmeticExpression;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\ConditionalTerm;
use Doctrine\ORM\Query\AST\InExpression;
use Doctrine\ORM\Query\AST\WhereClause;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\AST\Subselect;
use Doctrine\ORM\Query\AST\RangeVariableDeclaration;
use Doctrine\ORM\Query\AST\ComparisonExpression;

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclConditionStorage;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAssociationCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\SubRequestAclConditionStorage;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\JoinAclCondition;

/**
 * Class AclWalker
 */
class AclWalker extends TreeWalkerAdapter
{
    const ORO_ACL_CONDITION = 'oro_acl.condition';

    const EXPECTED_TYPE = 12;

    /**
     * @inheritdoc
     */
    public function walkSelectStatement(SelectStatement $AST)
    {
        /** @var Query $query */
        $query = $this->_getQuery();
        if ($query->hasHint(self::ORO_ACL_CONDITION)) {
            /** @var AclConditionStorage $aclCondition */
            $aclCondition = $query->getHint(self::ORO_ACL_CONDITION);

            if (!$aclCondition->isEmpty()) {
                if (!is_null($aclCondition->getWhereConditions()) && count($aclCondition->getWhereConditions())) {
                    $this->addAclToWhereClause($AST, $aclCondition->getWhereConditions());
                }
                if (!is_null($aclCondition->getJoinConditions()) && count($aclCondition->getJoinConditions())) {
                    $this->addAclToJoinClause($AST, $aclCondition->getJoinConditions());
                }

                $this->processSubRequests($AST, $aclCondition);
            }
        }

        return $AST;
    }

    /**
     * process subselects of query
     *
     * @param SelectStatement $AST
     * @param AclConditionStorage $aclCondition
     */
    protected function processSubRequests(SelectStatement $AST, AclConditionStorage $aclCondition)
    {
        if (!is_null($aclCondition->getSubRequests())) {
            $subRequests = $aclCondition->getSubRequests();

            foreach ($subRequests as $subRequest) {
                /** @var SubRequestAclConditionStorage $subRequest */
                $subselect = $AST
                    ->whereClause
                    ->conditionalExpression
                    ->conditionalFactors[$subRequest->getFactorId()]
                    ->simpleConditionalExpression
                    ->subselect;

                if (!is_null($subRequest->getWhereConditions()) && count($subRequest->getWhereConditions())) {
                    $this->addAclToWhereClause($subselect, $subRequest->getWhereConditions());
                }
                if (!is_null($subRequest->getJoinConditions()) && count($subRequest->getJoinConditions())) {
                    $this->addAclToJoinClause($subselect, $subRequest->getJoinConditions());
                }
            }
        }
    }

    /**
     * work with join statements of query
     *
     * @param SelectStatement $AST
     * @param array $joinConditions
     */
    protected function addAclToJoinClause($AST, array $joinConditions)
    {
        if ($AST instanceof Subselect) {
            $fromClause = $AST->subselectFromClause;
        } else {
            $fromClause = $AST->fromClause;
        }
        foreach ($joinConditions as $condition) {
            /** @var Join $join */
            $join = $fromClause
                ->identificationVariableDeclarations[$condition->getFromKey()]
                ->joins[$condition->getJoinKey()];
            if (!($condition instanceof JoinAssociationCondition)) {
                /** @var JoinAclCondition $condition */
                $conditionalFactor = $this->getConditionalFactor($condition);
                $aclConditionalFactors = array($conditionalFactor);
                if ($join->conditionalExpression instanceof ConditionalPrimary) {
                    array_unshift($aclConditionalFactors, $join->conditionalExpression);
                    $join->conditionalExpression = new ConditionalTerm(
                        $aclConditionalFactors
                    );
                } else {
                    $join->conditionalExpression->conditionalFactors = array_merge(
                        $join->conditionalExpression->conditionalFactors,
                        $aclConditionalFactors
                    );
                }
            } else {
                $fromClause
                    ->identificationVariableDeclarations[$condition->getFromKey()]
                    ->joins[$condition->getJoinKey()] = $this->getJoinFromJoinAssociationCondition($join, $condition);
            }
        }
    }

    /**
     * Generate Join condition for join wothout "on" statement
     *
     * @param Join $join
     * @param JoinAssociationCondition $condition
     * @return Join
     */
    protected function getJoinFromJoinAssociationCondition(Join $join, JoinAssociationCondition $condition)
    {
        $joinAssociationPathExpression = $join->joinAssociationDeclaration->joinAssociationPathExpression;

        $leftExpression = new ArithmeticExpression();
        $pathExpression = new PathExpression(
            self::EXPECTED_TYPE,
            $joinAssociationPathExpression->identificationVariable,
            $joinAssociationPathExpression->associationField
        );
        $pathExpression->type = PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION;
        $leftExpression->simpleArithmeticExpression = $pathExpression;

        $conditionalFactors = [];

        $joinConditionsArray = is_array($condition->getJoinConditions()) ? $condition->getJoinConditions() : array($condition->getJoinConditions());
        foreach ($joinConditionsArray as $joinCondition) {
            $rightExpression = new ArithmeticExpression();
            $pathExpression = new PathExpression(
                self::EXPECTED_TYPE,
                $condition->getEntityAlias(),
                is_string($joinCondition) ? $joinCondition : $joinCondition['referencedColumnName']
            );
            $pathExpression->type = PathExpression::TYPE_STATE_FIELD;
            $rightExpression->simpleArithmeticExpression = $pathExpression;
            $factor = new ConditionalPrimary();
            $factor->simpleConditionalExpression = new ComparisonExpression($leftExpression, '=', $rightExpression);
            $conditionalFactors[] = $factor;
        }
        $conditionalFactors[] = $this->getConditionalFactor($condition);
        $associationDeclaration = new RangeVariableDeclaration($condition->getEntityClass(), $condition->getEntityAlias());

        $newJoin = new Join($join->joinType, $associationDeclaration);
        $newJoin->conditionalExpression = new ConditionalTerm($conditionalFactors);

         return $newJoin;
    }

    /**
     * work with "where" statement of query
     *
     * @param SelectStatement $AST
     * @param array $whereConditions
     */
    protected function addAclToWhereClause($AST, array $whereConditions)
    {
        $aclConditionalFactors = [];

        foreach ($whereConditions as $whereCondition) {
            $aclConditionalFactors[] = $this->getConditionalFactor($whereCondition);
        }

        if (!empty($aclConditionalFactors)) {
            // we have query without 'where' part
            if ($AST->whereClause === null) {
                $AST->whereClause = new WhereClause(new ConditionalTerm($aclConditionalFactors));
            } else {
                // 'where' part has only one condition
                if ($AST->whereClause->conditionalExpression instanceof ConditionalPrimary) {
                    array_unshift($aclConditionalFactors, $AST->whereClause->conditionalExpression);
                    $AST->whereClause->conditionalExpression = new ConditionalTerm(
                        $aclConditionalFactors
                    );
                } else {
                    // 'where' part has more than one condition
                    if (isset($AST->whereClause->conditionalExpression->conditionalFactors)) {
                        $AST->whereClause->conditionalExpression->conditionalFactors = array_merge(
                            $AST->whereClause->conditionalExpression->conditionalFactors,
                            $aclConditionalFactors
                        );
                    } else {
                        $AST->whereClause->conditionalExpression->conditionalTerms = array_merge(
                            $AST->whereClause->conditionalExpression->conditionalTerms,
                            $aclConditionalFactors
                        );
                    }
                }
            }
        }
    }

    /**
     * Get acl access level condition
     *
     * @param AclCondition $condition
     * @return ConditionalPrimary
     */
    protected function getConditionalFactor(AclCondition $condition)
    {
        if ($condition->getValue() == null && $condition->getEntityField() == null) {
            $expression = $this->getAccessDeniedExpression();
        } else {
            $expression = $this->getInExpression($condition);
        }

        $resultCondition = new ConditionalPrimary();
        $resultCondition->simpleConditionalExpression = $expression;

        return $resultCondition;
    }

    /**
     * Generates "1=0" expression
     *
     * @return ComparisonExpression
     */
    protected function getAccessDeniedExpression()
    {
        $leftExpression = new ArithmeticExpression();
        $leftExpression->simpleArithmeticExpression = new Literal(Literal::NUMERIC, 1);
        $rightExpression = new ArithmeticExpression();
        $rightExpression->simpleArithmeticExpression = new Literal(Literal::NUMERIC, 0);

        return new ComparisonExpression($leftExpression, '=', $rightExpression);
    }

    /**
     * generate "in()" expression
     *
     * @param AclCondition $whereCondition
     * @return InExpression
     */
    protected function getInExpression(AclCondition $whereCondition)
    {
        $arithmeticExpression = new ArithmeticExpression();
        $arithmeticExpression->simpleArithmeticExpression = $this->getPathExpression($whereCondition);

        $expression = new InExpression($arithmeticExpression);
        $expression->literals = $this->getLiterals($whereCondition);

        return $expression;
    }

    /**
     * Generate path expression
     *
     * @param AclCondition $whereCondition
     * @return PathExpression
     */
    protected function getPathExpression(AclCondition $whereCondition)
    {
        $pathExpression = new PathExpression(
            self::EXPECTED_TYPE,
            $whereCondition->getEntityAlias(),
            $whereCondition->getEntityField()
        );

        $pathExpression->type = PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION;

        return $pathExpression;
    }

    /**
     * Get array with literal from acl condition value array
     *
     * @param AclCondition $whereCondition
     * @return array
     */
    protected function getLiterals(AclCondition $whereCondition)
    {
        $literals = [];

        if (!is_array($whereCondition->getValue())) {
            $whereCondition->setValue(array($whereCondition->getValue()));
        }
        foreach ($whereCondition->getValue() as $value) {
            $literals[] = new Literal(Literal::NUMERIC, $value);
        }

        return $literals;
    }
}
