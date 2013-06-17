<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SearchBundle\Datagrid\EntityResultListener;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;
use Oro\Bundle\SearchBundle\Tests\Unit\Datagrid\Stub\Category;
use Oro\Bundle\SearchBundle\Tests\Unit\Datagrid\Stub\Product;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

class EntityResultListenerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATAGRID_NAME = 'test_datagrid_name';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $productStubs;

    /**
     * @var array
     */
    protected $categoryStubs;

    /**
     * @var ClassMetadataFactory
     */
    protected $stubMetadata;

    /**
     * @param $datagridName
     * @return DatagridInterface
     */
    protected function getDatagridMock($datagridName)
    {
        $datagrid = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getName')
        );
        $datagrid->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($datagridName));

        return $datagrid;
    }

    public function testProcessResultNotMatchedDatagrid()
    {
        $datagrid = $this->getDatagridMock('random_datagrid_name');

        $registry = $this->getMockForAbstractClass(
            'Symfony\Bridge\Doctrine\RegistryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getManager')
        );
        $registry->expects($this->never())
            ->method('getManager');

        $event = new ResultDatagridEvent($datagrid);

        $eventListener = new EntityResultListener($registry, self::TEST_DATAGRID_NAME);
        $eventListener->processResult($event);
    }

    /**
     * Prepare entity manager mock
     */
    protected function prepareEntityManager()
    {
        $this->entityManager = $this->getMock(
            'Doctrine\ORM\EntityManager',
            array('getMetadataFactory', 'getRepository'),
            array(),
            '',
            false
        );
    }

    /**
     * Prepare stub data and mocks
     *
     * @return array
     */
    protected function prepareStubData()
    {
        // create product stubs
        $productEntities = array();
        for ($i = 1; $i <= 5; $i++) {
            $indexerItem = new Item($this->entityManager, Product::getEntityName(), $i);
            $entity = new Product($i);
            $productEntities[] = $entity;

            $this->productStubs[] = array(
                'indexer_item' => $indexerItem,
                'entity' => $entity,
            );
        }

        $productMetadata = new ClassMetadata(Product::getEntityName());
        $productMetadata->setIdentifier(array('id'));
        $reflectionProperty = new \ReflectionProperty(
            'Oro\Bundle\SearchBundle\Tests\Unit\Datagrid\Stub\Product',
            'id'
        );
        $reflectionProperty->setAccessible(true);
        $productMetadata->reflFields['id'] = $reflectionProperty;

        // create category stubs
        $categoryEntities = array();
        for ($i = 1; $i <= 3; $i++) {
            $indexerItem = new Item($this->entityManager, Category::getEntityName(), $i);
            $entity = new Category($i);
            $categoryEntities[] = $entity;

            $this->categoryStubs[] = array(
                'indexer_item' => $indexerItem,
                'entity' => $entity,
            );
        }

        $categoryMetadata = new ClassMetadata(Category::getEntityName());
        $categoryMetadata->setIdentifier(array('id'));
        $reflectionProperty = new \ReflectionProperty(
            'Oro\Bundle\SearchBundle\Tests\Unit\Datagrid\Stub\Category',
            'id'
        );
        $reflectionProperty->setAccessible(true);
        $categoryMetadata->reflFields['id'] = $reflectionProperty;

        // create metadata factory for stubs
        $this->stubMetadata = new ClassMetadataFactory($this->entityManager);
        $this->stubMetadata->setMetadataFor(Product::getEntityName(), $productMetadata);
        $this->stubMetadata->setMetadataFor(Category::getEntityName(), $categoryMetadata);

        $this->entityManager->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($this->stubMetadata));

        return array(
            Product::getEntityName()  => $productEntities,
            Category::getEntityName() => $categoryEntities,
        );
    }

    /**
     * Prepare repository for specific entity
     *
     * @param  string           $entityName
     * @param  array            $entities
     * @param  array            $entityIds
     * @return EntityRepository
     */
    protected function prepareEntityRepository($entityName, array $entities, array $entityIds)
    {
        $query = $this->getMockForAbstractClass(
            'Doctrine\ORM\AbstractQuery',
            array($this->entityManager),
            '',
            true,
            true,
            true,
            array('getResult')
        );
        $query->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($entities));

        $queryBuilder = $this->getMock(
            'Doctrine\ORM\QueryBuilder',
            array('where', 'getQuery'),
            array($this->entityManager)
        );
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with(new Func('e.id IN', $entityIds));
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $repository = $this->getMock(
            'Doctrine\ORM\EntityRepository',
            array('createQueryBuilder'),
            array($this->entityManager, $this->stubMetadata->getMetadataFor($entityName))
        );
        $repository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('e')
            ->will($this->returnValue($queryBuilder));

        return $repository;
    }

    /**
     * Prepare repositories with stub data
     *
     * @param array $productEntities
     * @param array $categoryEntities
     */
    protected function prepareRepositories(array $productEntities, array $categoryEntities)
    {
        $productRepository = $this->prepareEntityRepository(
            Product::getEntityName(),
            $productEntities,
            array(1,2,3,4,5)
        );

        $categoryRepository = $this->prepareEntityRepository(
            Category::getEntityName(),
            $categoryEntities,
            array(1,2,3)
        );

        // entity manager behaviour
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    array(
                        array(Product::getEntityName(), $productRepository),
                        array(Category::getEntityName(), $categoryRepository),
                    )
                )
            );
    }

    public function testProcessResult()
    {
        // prepare mocks
        $this->prepareEntityManager();
        $stubEntities = $this->prepareStubData();
        $this->prepareRepositories(
            $stubEntities[Product::getEntityName()],
            $stubEntities[Category::getEntityName()]
        );

        $datagrid = $this->getDatagridMock(self::TEST_DATAGRID_NAME);

        $registry = $this->getMockForAbstractClass(
            'Symfony\Bridge\Doctrine\RegistryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getManager')
        );
        $registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        // get indexer items
        $indexerRows = array();
        foreach (array($this->productStubs, $this->categoryStubs) as $stubElements) {
            foreach ($stubElements as $stubElement) {
                $indexerRows[] = $stubElement['indexer_item'];
            }
        }

        $event = new ResultDatagridEvent($datagrid);
        $event->setRows($indexerRows);

        $expectedRows = array_merge($this->productStubs, $this->categoryStubs);

        // test
        $eventListener = new EntityResultListener($registry, self::TEST_DATAGRID_NAME);
        $eventListener->processResult($event);
        $this->assertEquals($expectedRows, $event->getRows());
    }
}
