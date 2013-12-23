<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Sorter\ORM\Flexible;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Pim\Bundle\GridBundle\Sorter\ORM\Flexible\FlexibleSorter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlexibleSorter
     */
    protected $flexibleSorter;

    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->flexibleRegistry = $this->getMockBuilder(
            'Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry'
        )->setMethods(array('getManager'))->getMock();

        $this->flexibleSorter = new FlexibleSorter($this->flexibleRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->flexibleRegistry);
        unset($this->flexibleSorter);
    }

    /**
     * Test related method
     */
    public function testInitialize()
    {
        $entityName = 'Test';

        $fieldDescription = $this->createFieldDescription($entityName);
        $flexibleManager = $this->createFlexibleManager();

        $this->flexibleRegistry
            ->expects($this->once())
            ->method('getManager')
            ->with($entityName)
            ->will($this->returnValue($flexibleManager));

        $this->flexibleSorter->initialize($fieldDescription);
        $this->assertAttributeEquals($flexibleManager, 'flexibleManager', $this->flexibleSorter);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Flexible entity sorter must have flexible entity name.
     */
    public function testInitializeError()
    {
        $fieldDescription = $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescription');
        $fieldDescription->expects($this->once())
            ->method('getOption')
            ->with('flexible_name');

        $this->flexibleSorter->initialize($fieldDescription);
    }

    /**
     * Test related method
     */
    public function testApply()
    {
        $this->markTestIncomplete('This test must be completed after grid refactoring.');

        $entityName = 'TestEntity';
        $fieldName = 'test_field';
        $direction = SorterInterface::DIRECTION_ASC;

        $fieldDescription = $this->createFieldDescription($entityName, $fieldName);
        $flexibleManager = $this->createFlexibleManager();

        $this->flexibleRegistry
            ->expects($this->once())
            ->method('getManager')
            ->with($entityName)
            ->will($this->returnValue($flexibleManager));

        $this->flexibleSorter->initialize($fieldDescription);

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $proxyQuery = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getQueryBuilder'))
            ->getMockForAbstractClass();
        $proxyQuery->expects($this->once())->method('getQueryBuilder')->will($this->returnValue($queryBuilder));

        $entityRepository = $this->createFlexibleEntityRepository();

        $entityRepository->expects($this->once())
            ->method('applySorterByAttribute')
            ->with($queryBuilder, $fieldName);

        $flexibleManager->expects($this->once())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($entityRepository));

        $entityRepository = $this->createAttributeRepository();
        $flexibleManager->expects($this->once())
            ->method('getAttributeRepository')
            ->will($this->returnValue($entityRepository));

        $this->flexibleSorter->apply($proxyQuery, $direction);
    }

    /**
     * @param string $flexibleName
     * @param string $fieldName
     *
     * @return FieldDescription|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldDescription($flexibleName, $fieldName = null)
    {
        $fieldDescription = $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescription');
        $fieldDescription->expects($this->any())
            ->method('getOption')
            ->with('flexible_name')
            ->will($this->returnValue($flexibleName));

        if ($fieldName) {
            $fieldDescription->expects($this->any())
                ->method('getFieldName')
                ->will($this->returnValue($fieldName));
        }

        return $fieldDescription;
    }

    /**
     * @return FlexibleManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFlexibleManager()
    {
        $flexibleManager = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getFlexibleRepository', 'getAttributeRepository'))
            ->getMock();

        return $flexibleManager;
    }

    /**
     * @return FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFlexibleEntityRepository()
    {
        $mock = $this->getMockBuilder(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository'
        )->disableOriginalConstructor()->setMethods(array('applySorterByAttribute'))->getMock();

        return $mock;
    }

    /**
     * @return FlexibleEntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeRepository()
    {
        $mock = $this->getMockBuilder(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository'
        )->disableOriginalConstructor()
        ->setMethods(array('findOneByEntityAndCode'))
        ->getMock();

        $attribute = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute'
        );

        $mock->expects($this->any())
            ->method('findOneByEntityAndCode')
            ->will($this->returnValue($attribute));

        return $mock;
    }
}
