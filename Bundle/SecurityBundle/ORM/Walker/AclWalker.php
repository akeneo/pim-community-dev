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

use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclConditionStorage;
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
            /** @var JoinAclCondition $condition */
            $conditionalFactor = $this->getConditionalFactor($condition);

            /** @var Join $join */
            $join = $fromClause
                ->identificationVariableDeclarations[$condition->getFromKey()]
                ->joins[$condition->getJoinKey()];

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
        }
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
                    $AST->whereClause->conditionalExpression->conditionalFactors = array_merge(
                        $AST->whereClause->conditionalExpression->conditionalFactors,
                        $aclConditionalFactors
                    );
                }
            }
        }
    }

    /**
     * @param AclCondition $condition
     * @return ConditionalPrimary
     */
    protected function getConditionalFactor(AclCondition $condition)
    {
        $expression = $this->getInExpression($condition);

        $resultCondition = new ConditionalPrimary();
        $resultCondition->simpleConditionalExpression = $expression;

        return $resultCondition;
    }

    /**
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
     * @param AclCondition $whereCondition
     * @return array
     */
    protected function getLiterals(AclCondition $whereCondition)
    {
        $literals = [];

        if (!is_array($whereCondition->getValue())) {
            $whereCondition->setValue(array($whereCondition->getValue()));
        }
        foreach ($whereCondition->getValue() as $value)
        {
            $literals[] = new Literal(Literal::NUMERIC, $value);
        }

        return $literals;
    }
}
