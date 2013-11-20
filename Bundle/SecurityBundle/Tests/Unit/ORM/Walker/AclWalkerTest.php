<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\Walker;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclWalker;

use Doctrine\Tests\OrmTestCase;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclCondition;
use Oro\Bundle\SecurityBundle\ORM\Walker\Condition\AclConditionStorage;

class AclWalkerTest
{
    /**
     * @var AclWalker
     */
    protected $walker;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $parserResult = $this->getMockBuilder('Doctrine\ORM\Query\ParserResult')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->_getTestEntityManager();

        $this->walker = new AclWalker($this->getQuery(), $parserResult, []);
    }

    public function testWalkSelectStatement()
    {
        $query = $this->getQuery()->setDQL('SELECT u from Doctrine\Tests\Models\CMS\CmsUser u');
        $condition = new AclConditionStorage([new AclCondition('u', 'id', [1, 2, 3])], []);

        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS,
            array_merge(
                $query->getHints(),
                array(AclHelper::ORO_ACL_WALKER)
            )
        );
        $query->setHint(AclWalker::ORO_ACL_CONDITION, $condition);

        var_dump($query->getAST());
        $ast = $this->walker->walkSelectStatement($query->getAST());
        var_dump($ast);die;
    }



    /**
     * @return Query
     */
    protected function getQuery()
    {
        return new Query($this->em);
    }
} 