<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\Manager;

use Pim\Bundle\CustomEntityBundle\Manager\OrmManager;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $repository;
    protected $doctrine;
    protected $propertyAccessor;
    protected $entityManager;
    protected $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['find', 'method', 'createQueryBuilder'])
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->doctrine->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('class'))
            ->will($this->returnValue($this->repository));
        $this->doctrine->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->manager = new OrmManager($this->doctrine, $this->propertyAccessor);
    }

    /**
     * Test related method
     */
    public function testCreate()
    {
        $this->propertyAccessor
            ->expects($this->at(0))
            ->method('setValue')
            ->with($this->isInstanceOf('stdClass'), $this->equalTo('key1'), $this->equalTo('val1'));
        $this->propertyAccessor
            ->expects($this->at(1))
            ->method('setValue')
            ->with($this->isInstanceOf('stdClass'), $this->equalTo('key2'), $this->equalTo('val2'));
        $this->propertyAccessor
            ->expects($this->exactly(2))
            ->method('setValue');
        $object = $this->manager->create(
            'stdClass',
            ['key1' => 'val1', 'key2' => 'val2']
        );
        $this->assertInstanceOf('stdClass', $object);
    }

    /**
     * Test related method
     */
    public function testFind()
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo('id'))
            ->will($this->returnValue('success'));
        $this->assertEquals('success', $this->manager->find('class', 'id'));
    }

    /**
     * @return array
     */
    public function getCreateQueryBuilderData()
    {
        return [
            'no_option'     => [[], 'createQueryBuilder', 't'],
            'with_options'  => [
                [
                    'query_builder_method'  => 'method',
                    'query_builder_alias'   => 'alias'
                ],
                'method',
                'alias'
            ],
        ];
    }

    /**
     * @param array  $options
     * @param string $method
     * @param string $alias
     *
     * @dataProvider getCreateQueryBuilderData
     */
    public function testCreateQueryBuilder($options, $method, $alias)
    {
        $this->repository->expects($this->once())
            ->method($method)
            ->with($this->equalTo($alias))
            ->will($this->returnValue('success'));
        $this->assertEquals('success', $this->manager->createQueryBuilder('class', $options));
    }

    /**
     * Test related method
     */
    public function testSave()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo('entity'));
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->manager->save('entity');
    }

    /**
     * Test related method
     */
    public function testRemove()
    {
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo('entity'));
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->manager->remove('entity');
    }
}
