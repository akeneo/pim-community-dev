<?php
namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker;

use Doctrine\ORM\EntityManager;
use Doctrine\Tests\OrmTestCase;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

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
     * @var EntityManager
     */
    protected $em;

    /**
     * @dataProvider dataProvider
     */
    public function testApply($queryBuilder, $conditions, $resultHandler)
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

                        return [];
                    }
                )
            );

        $this->helper = new AclHelper($this->conditionBuilder);

        $resultHandler($this->helper->apply($queryBuilder)->getHints());
    }

    public function dataProvider()
    {
        $this->em = $this->_getTestEntityManager();
        return [
            [
                $this->getQueryBuilder()
                    ->select('t')
                    ->from('Doctrine\Tests\Models\CMS\CmsUser', 't'),
                [
                    'Doctrine\Tests\Models\CMS\CmsUser' => [
                        'id',
                        [1,2,3]
                    ]
                ],
                function ($hints) {
                    $whereCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getWhereConditions()[0];
                    $this->assertEquals('t', $whereCondition->getEntityAlias());
                    $this->assertEquals('id', $whereCondition->getEntityField());
                    $this->assertEquals([1,2,3], $whereCondition->getValue());
                }
            ],
            [
                $this->getQueryBuilder()
                    ->select('u')
                    ->from('Doctrine\Tests\Models\CMS\CmsUser', 'u'),
                [],
                function ($hints) {
                    $whereCondition = $hints[AclWalker::ORO_ACL_CONDITION]->getWhereConditions()[0];
                    $this->assertEquals('u', $whereCondition->getEntityAlias());
                    $this->assertNull($whereCondition->getEntityField());
                    $this->assertNull($whereCondition->getValue());
                }
            ]
        ];
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return new QueryBuilder($this->em);
    }
} 