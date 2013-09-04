<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\ORM\SqlFilter;

use Oro\Bundle\SecurityBundle\ORM\SqlFilter\OwnershipFilter;

class OwnershipFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OwnershipFilter
     */
    protected $filter;

    protected function setUp()
    {
        $filterCollection = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\Query\FilterCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())->method('getFilters')->will($this->returnValue($filterCollection));
        $this->filter = new OwnershipFilter($entityManager);
    }

    public function testSetBuilderAndUserParameter()
    {
        $builder = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\OwnershipSqlFilterBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())->method('getUserId')->will($this->returnValue(1));

        $this->filter->setBuilder($builder);
        $this->assertAttributeEquals($builder, 'builder', $this->filter);

        $this->filter->setUserParameter();
        $this->assertAttributeEquals(
            array('user_id' => array('value' => 1, 'type' => 'integer')),
            'parameters',
            $this->filter
        );
    }
}
