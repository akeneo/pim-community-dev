<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Engine;

use Oro\Bundle\SearchBundle\Engine\Orm;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Entity\Item;
use Oro\Bundle\SearchBundle\Query\Result;

use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Manufacturer;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Attribute;

class OrmTest extends \PHPUnit_Framework_TestCase
{
    private $product;

    /**
     * @var \Oro\Bundle\SearchBundle\Engine\Orm
     */
    private $orm;
    private $om;
    private $container;
    private $mapper;
    private $dispatcher;

    public function setUp()
    {
        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manufacturer = new Manufacturer();
        $manufacturer->setName('adidas');

        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = new Product();
        $this->product->setName('test product')
            ->setCount(10)
            ->setPrice(150)
            ->setManufacturer($manufacturer)
            ->setDescription('description')
            ->setCreateDate(new \DateTime());

        $this->orm = new Orm($this->om, $this->dispatcher, $this->container, $this->mapper, true);
    }

    public function testDoSearch()
    {
        $query = new Query();
        $query->createQuery(Query::SELECT)
            ->from('test')
            ->andWhere('name', '~', 'test value', Query::TYPE_TEXT);

        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->om->expects($this->once())
            ->method('persist');

        $this->om->expects($this->once())
            ->method('flush');

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->once())
            ->method('setDriversClasses');

        $result = $this->orm->search($query);

        $this->assertEquals(0, $result->getRecordsCount());
        $searchOptions = $result->getQuery()->getOptions();

        $this->assertEquals('name', $searchOptions[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_CONTAINS, $searchOptions[0]['condition']);
        $this->assertEquals('test value', $searchOptions[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchOptions[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchOptions[0]['type']);
    }

    public function testDeleteNonExistsEntity()
    {
        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $searchRepo->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(false));

        $this->assertEquals(false, $this->orm->delete($this->product, true));
    }

    public function testDelete()
    {
        $query = $this->getMock(
            'Doctrine\ORM\AbstractQuery',
            array('getSQL', 'setMaxResults', 'getOneOrNullResult', 'setParameter', '_doExecute'),
            array(),
            '',
            false
        );

        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->container->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->any())
            ->method('setDriversClasses');

        $item = new Item();

        $searchRepo->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($item));

        $this->om->expects($this->any())
            ->method('remove')
            ->with($this->equalTo($item));

        $this->om->expects($this->any())
            ->method('flush');

        $this->om->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setParameter')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(0));

        $this->orm->delete($this->product, true);
        $this->orm->delete($this->product, false);
    }

    public function testSave()
    {
        $query = $this->getMock(
            'Doctrine\ORM\AbstractQuery',
            array('getSQL', 'setMaxResults', 'getOneOrNullResult', 'setParameter', '_doExecute'),
            array(),
            '',
            false
        );

        $this->mapper->expects($this->any())
            ->method('mapObject')
            ->will($this->returnValue(array(array())));

        $this->om->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setParameter')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(0));

        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->container->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->any())
            ->method('setDriversClasses');

        $meta = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $reflectionProperty = $this
            ->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->any())
            ->method('getReflectionProperty')
            ->will($this->returnValue($reflectionProperty));

        $this->om->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($meta));

        $meta->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));

        $reflectionProperty->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1));

        $uow = $this
            ->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $uow->expects($this->any())
            ->method('computeFields');

        $this->orm->save($this->product, true, true);
        $this->orm->save($this->product, false);

        $this->mapper->expects($this->once())
            ->method('getEntityConfig')
            ->will($this->returnValue(array('alias' => 'test')));

        $manufacturer = new Manufacturer();
        $manufacturer->setName('reebok');
        $manufacturer->addProduct($this->product);
        $this->orm->save($manufacturer, true);
    }

    public function testFailedSave()
    {
        $reflectionProperty = $this
            ->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();

        $reflectionProperty->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(1));

        $meta = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($meta));

        $meta->expects($this->any())
            ->method('getReflectionProperty')
            ->will($this->returnValue($reflectionProperty));

        $meta->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));

        $this->assertEquals(false, $this->orm->save(new Attribute(), true));
    }

    public function testGetMapper()
    {
        $this->assertEquals($this->mapper, $this->orm->getMapper());
    }
}
