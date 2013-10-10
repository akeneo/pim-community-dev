<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Formatter;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SearchBundle\Tests\Unit\Formatter\Stub\Category;
use Oro\Bundle\SearchBundle\Tests\Unit\Formatter\Stub\Product;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Formatter\ResultFormatter;

class ResultFormatterTest extends \PHPUnit_Framework_TestCase
{
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
            $productEntities[$i] = $entity;

            $this->productStubs[$i] = array(
                'indexer_item' => $indexerItem,
                'entity' => $entity,
            );
        }

        $productMetadata = new ClassMetadata(Product::getEntityName());
        $productMetadata->setIdentifier(array('id'));
        $reflectionProperty = new \ReflectionProperty(
            'Oro\Bundle\SearchBundle\Tests\Unit\Formatter\Stub\Product',
            'id'
        );
        $reflectionProperty->setAccessible(true);
        $productMetadata->reflFields['id'] = $reflectionProperty;

        // create category stubs
        $categoryEntities = array();
        for ($i = 1; $i <= 3; $i++) {
            $indexerItem = new Item($this->entityManager, Category::getEntityName(), $i);
            $entity = new Category($i);
            $categoryEntities[$i] = $entity;

            $this->categoryStubs[$i] = array(
                'indexer_item' => $indexerItem,
                'entity' => $entity,
            );
        }

        $categoryMetadata = new ClassMetadata(Category::getEntityName());
        $categoryMetadata->setIdentifier(array('id'));
        $reflectionProperty = new \ReflectionProperty(
            'Oro\Bundle\SearchBundle\Tests\Unit\Formatter\Stub\Category',
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

    /**
     * Prepare and get stub entities
     *
     * @return array
     */
    protected function prepareStubEntities()
    {
        $stubEntities = $this->prepareStubData();
        $this->prepareRepositories(
            $stubEntities[Product::getEntityName()],
            $stubEntities[Category::getEntityName()]
        );

        return $stubEntities;
    }

    /**
     * Get list of test indexer rows
     *
     * @return array
     */
    protected function getIndexerRows()
    {
        $indexerRows = array();
        foreach (array($this->productStubs, $this->categoryStubs) as $stubElements) {
            foreach ($stubElements as $stubElement) {
                $indexerRows[] = $stubElement['indexer_item'];
            }
        }

        return $indexerRows;
    }

    /**
     * Get list of ordered result entities
     *
     * @return array
     */
    protected function getOrderedEntities()
    {
        $entities = array();
        foreach (array($this->productStubs, $this->categoryStubs) as $stubElements) {
            foreach ($stubElements as $stubElement) {
                $entities[] = $stubElement['entity'];
            }
        }

        return $entities;
    }

    public function testGetResultEntities()
    {
        $this->prepareEntityManager();
        $expectedResult = $this->prepareStubEntities();

        /** @var $indexer Indexer */
        $indexer = $this->getMock('Oro\Bundle\SearchBundle\Engine\Indexer', array(), array(), '', false);
        $indexerRows = $this->getIndexerRows();

        $resultFormatter = new ResultFormatter($this->entityManager, $indexer);
        $actualResult = $resultFormatter->getResultEntities($indexerRows);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetOrderedResultEntities()
    {
        $this->prepareEntityManager();
        $this->prepareStubEntities();

        /** @var $indexer Indexer */
        $indexer = $this->getMock('Oro\Bundle\SearchBundle\Engine\Indexer', array(), array(), '', false);
        $indexerRows = $this->getIndexerRows();

        $resultFormatter = new ResultFormatter($this->entityManager, $indexer);
        $actualResult = $resultFormatter->getOrderedResultEntities($indexerRows);

        $expectedResult = $this->getOrderedEntities();

        $this->assertEquals($expectedResult, $actualResult);
    }
}
