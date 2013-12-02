<?php
namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker;

use Doctrine\Tests\OrmTestCase;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\SelectStatement;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclWalker;
use Oro\Bundle\SecurityBundle\ORM\Walker\OwnershipConditionDataBuilder;

class AclHelperTest extends OrmTestCase
{
    /**
     * @var AclHelper
     */
    protected $helper;

    /**
     * @var OwnershipConditionDataBuilder
     */
    protected $conditionBuilder;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var AclWalker
     */
    protected $walker;

    /**
     * @dataProvider dataProvider
     */
    public function testApply(QueryBuilder $queryBuilder, $conditions, $resultHandler, $walkerResult)
    {
        $this->conditionBuilder = $this->getMockBuilder(
            'Oro\Bundle\SecurityBundle\ORM\Walker\OwnershipConditionDataBuilder'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionBuilder->expects($this->any())
            ->method('getAclConditionData')
            ->will(
                $this->returnCallback(
                    function ($entityName, $permission) use ($conditions) {
                        if (isset($conditions[$entityName])) {

                            return $conditions[$entityName];
                        }

                        return null;
                    }
                )
            );

        $this->helper = new AclHelper($this->conditionBuilder);
        $query = $this->helper->apply($queryBuilder);
        $this->$resultHandler($query->getHints());

        $parserResult = $this->getMockBuilder('Doctrine\ORM\Query\ParserResult')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals($query->getDQL(), $queryBuilder->getDQL());

        $this->walker = new AclWalker($query, $parserResult, []);
        $resultAst = $this->walker->walkSelectStatement($query->getAST());

        $this->$walkerResult($resultAst);
    }

    public function dataProvider()
    {
        return [
            [
                $this->getRequest0(),
                [],
                'resultHelper0',
                'resultWalker0'
            ],
            [
               $this->getRequest1(),
                [
                    'Doctrine\Tests\Models\CMS\CmsUser' => ['id', [1 ,2, 3]],
                    'Doctrine\Tests\Models\CMS\CmsAddress' => ['id', [1]]
                ],
                'resultHelper1',
                'resultWalker1'
            ],
            [
                $this->getRequest2(),
                [
                    'Doctrine\Tests\Models\CMS\CmsUser' => [],
                    'Doctrine\Tests\Models\CMS\CmsAddress' => ['id', [1]]
                ],
                'resultHelper2',
                'resultWalker2'
            ],
            [
                $this->getRequest3(),
                [
                    'Doctrine\Tests\Models\CMS\CmsArticle' => ['id', [10]],
                    'Doctrine\Tests\Models\CMS\CmsComment' => ['id', [100]],
                    'Doctrine\Tests\Models\CMS\CmsUser' => ['id', [3, 2, 1]],
                    'Doctrine\Tests\Models\CMS\CmsAddress' => ['id', [150]]
                ],
                'resultHelper3',
                'resultWalker3'
            ]
        ];
    }

    protected function getRequest0()
    {
        return $this->getQueryBuilder()
            ->select('u')
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u');
    }

    protected function resultHelper0($hints)
    {
        $whereCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getWhereConditions()[0];
        $this->assertEquals('u', $whereCondition->getEntityAlias());
        $this->assertNull($whereCondition->getEntityField());
        $this->assertNull($whereCondition->getValue());
    }

    protected function resultWalker0(SelectStatement $resultAst)
    {
        // 1=0 expression
        $expression = $resultAst
            ->whereClause
            ->conditionalExpression
            ->conditionalFactors[0]
            ->simpleConditionalExpression;

        $leftExpression = $expression->leftExpression;
        $rightExpression = $expression->rightExpression;
        $this->assertEquals(1, $leftExpression->simpleArithmeticExpression->value);
        $this->assertEquals('=', $expression->operator);
        $this->assertEquals(0, $rightExpression->simpleArithmeticExpression->value);
    }

    protected function getRequest1()
    {
        return $this->getQueryBuilder()
            ->select('u')
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->join('u.address', 'address');
    }

    protected function resultHelper1($hints)
    {
        $whereCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getWhereConditions()[0];
        $this->assertEquals('u', $whereCondition->getEntityAlias());
        $this->assertEquals('id', $whereCondition->getEntityField());
        $this->assertEquals([1, 2, 3], $whereCondition->getValue());
        $joinCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getJoinConditions()[0];
        $this->assertEquals('address', $joinCondition->getEntityAlias());
        $this->assertEquals('Doctrine\Tests\Models\CMS\CmsAddress', $joinCondition->getEntityClass());
        $this->assertEquals([1], $joinCondition->getValue());
    }

    protected function resultWalker1(SelectStatement $resultAst)
    {
        $expression = $resultAst
            ->whereClause
            ->conditionalExpression
            ->conditionalFactors[0]
            ->simpleConditionalExpression;
        $this->assertEquals([1, 2, 3], $this->collectLiterals($expression->literals));
        $this->assertEquals('u', $expression->expression->simpleArithmeticExpression->identificationVariable);
        $join = $resultAst->fromClause->identificationVariableDeclarations[0]->joins[0];
        $this->assertEquals('Doctrine\Tests\Models\CMS\CmsAddress', $join->joinAssociationDeclaration->abstractSchemaName);
        $this->assertEquals('address', $join->joinAssociationDeclaration->aliasIdentificationVariable);
    }

    protected function getRequest2()
    {
        return $this->getQueryBuilder()
            ->select('u')
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->join('u.address', 'address', 'WITH', 'address.id = u.id');
    }

    protected function resultHelper2($hints)
    {
        $this->assertEmpty($hints[AclWalker::ORO_ACL_CONDITION]->getWhereConditions());
        $joinCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getJoinConditions()[0];
        $this->assertEquals([1], $joinCondition->getValue());

    }

    protected function resultWalker2(SelectStatement $resultAst)
    {
        $this->assertNull($resultAst->whereClause);
        $join = $resultAst->fromClause->identificationVariableDeclarations[0]->joins[0];
        $this->assertEquals('Doctrine\Tests\Models\CMS\CmsAddress', $join->joinAssociationDeclaration->abstractSchemaName);
        $this->assertEquals(
            [1],
            $this->collectLiterals(
                $join->conditionalExpression
                    ->conditionalFactors[0]
                    ->simpleConditionalExpression
                    ->literals
            )
        );
    }

    protected function getRequest3()
    {
        $subRequest = $this->getQueryBuilder()
            ->select('users.id')
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'users')
            ->join('users.articles', 'articles')
            ->join('articles.comments', 'comments')
            ->join('Doctrine\Tests\Models\CMS\CmsAddress', 'address', 'WITH', 'address.user = users.id AND address = 1')
            ->where('comments.id in (1, 2, 3)');

        $qb = $this->getQueryBuilder();
        $qb->select('u')
            ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u')
            ->where(
                $qb->expr()->in('u.id', $subRequest->getDQL())
            );

        return $qb;
    }

    protected function resultHelper3($hints)
    {
        $conditions = $hints[AclWalker::ORO_ACL_CONDITION];
        $this->assertEmpty($conditions->getJoinConditions());
        $whereCondition = $conditions->getWhereConditions()[0];
        $this->assertEquals([3, 2, 1], $whereCondition->getValue());
        $subRequest = $conditions->getSubRequests()[0];
        $this->assertEquals([3, 2, 1], $subRequest->getWhereConditions()[0]->getValue());
        $this->assertEquals('Doctrine\Tests\Models\CMS\CmsArticle', $subRequest->getJoinConditions()[0]->getEntityClass());
        $this->assertEquals('Doctrine\Tests\Models\CMS\CmsComment', $subRequest->getJoinConditions()[1]->getEntityClass());
        $this->assertEquals([150], $subRequest->getJoinConditions()[2]->getValue());
    }

    protected function resultWalker3(SelectStatement $resultAst)
    {
        $whereExpression = $resultAst
            ->whereClause
            ->conditionalExpression
            ->conditionalFactors[1]
            ->simpleConditionalExpression;
        $this->assertEquals([3, 2, 1], $this->collectLiterals($whereExpression->literals));
        $subselect = $resultAst->whereClause
            ->conditionalExpression
            ->conditionalFactors[0]
            ->simpleConditionalExpression
            ->subselect;
        $expression = $subselect->whereClause->conditionalExpression->conditionalFactors[1]->simpleConditionalExpression;
        $this->assertEquals([3, 2, 1], $this->collectLiterals($expression->literals));
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return new QueryBuilder($this->_getTestEntityManager());
    }

    /**
     * Make array with literals values
     *
     * @param array $literals
     * @return array
     */
    protected function collectLiterals(array $literals)
    {
        $result = [];
        foreach ($literals as $literal) {
            $result[] = $literal->value;
        }

        return $result;
    }
}
