<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class IndexerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Oro\Bundle\SearchBundle\Engine\Indexer
     */
    protected $indexService;
    protected $om;
    protected $mapper;
    protected $repository;
    protected $connector;
    protected $dispatcher;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroTestBundle:test'))
            ->will($this->returnValue($this->repository));

        $this->connector = $this->getMockForAbstractClass(
            'Oro\Bundle\SearchBundle\Engine\AbstractEngine',
            array(
                 $this->om,
                 $this->dispatcher,
                 false
            )
        );

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connector->expects($this->any())
            ->method('searchQuery')
            ->will($this->returnValue(array()));

        $this->config = array(
            'Oro\Bundle\DataBundle\Entity\Product' => array(
                'alias' => 'test_alias',
                'label' => 'test product',
                'fields' => array(
                    array(
                        'name' => 'name',
                        'target_type' => 'string',
                        'target_fields' => array('name', 'all_data')
                    ),
                    array(
                        'name' => 'description',
                        'target_type' => 'string',
                        'target_fields' => array('description', 'all_data')
                    ),
                    array(
                        'name' => 'price',
                        'target_type' => 'decimal',
                        'target_fields' => array('price')
                    ),
                    array(
                        'name' => 'count',
                        'target_type' => 'integer',
                        'target_fields' => array('count')
                    ),
                    array(
                        'name' => 'createDate',
                        'target_type' => 'datetime',
                        'target_fields' => array('create_date')
                    ),
                    array(
                        'name' => 'manufacturer',
                        'relation_type' => 'to',
                        'relation_fields' => array(
                            array(
                                'name' => 'name',
                                'target_type' => 'string',
                                'target_fields' => array('manufacturer', 'all_data')
                            )
                        )
                    ),
                )
            )
        );

        $this->indexService = new Indexer(
            $this->om,
            $this->connector,
            $this->mapper
        );
    }

    /**
     * Get query builder with select instance
     */
    public function testSelect()
    {
        $this->mapper->expects($this->once())
            ->method('getMappingConfig')
            ->will($this->returnValue($this->config));

        $select = $this->indexService->select();
        $this->assertEquals('select', $select->getQuery());
    }

    /**
     * Run query with query builder
     */
    public function testQuery()
    {
        $this->mapper->expects($this->once())
            ->method('getMappingConfig')
            ->will($this->returnValue($this->config));

        $select = $this->indexService->select();

        $resultItem = new Item($this->om);
        $this->connector->expects($this->once())
            ->method('doSearch')
            ->with($select)
            ->will($this->returnValue(array('results' => array($resultItem), 'records_count' => 1)));

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(PrepareResultItemEvent::EVENT_NAME, new PrepareResultItemEvent($resultItem));

        $this->indexService->query($select);
    }

    /**
     * Run simple search
     */
    public function testSimpleSearch()
    {
        $this->connector->expects($this->any())
            ->method('doSearch')
            ->will($this->returnValue(array('results' => array(), 'records_count' => 10)));

        $this->mapper->expects($this->any())
            ->method('getMappingConfig')
            ->will($this->returnValue($this->config));

        $select = $this->indexService->simpleSearch('test', 0, 0);
        $query = $select->getQuery();
        $from = $query->getFrom();
        $searchCondition = $query->getOptions();

        $this->assertEquals('*', $from[0]);
        $this->assertEquals(Indexer::TEXT_ALL_DATA_FIELD, $searchCondition[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_CONTAINS, $searchCondition[0]['condition']);
        $this->assertEquals('test', $searchCondition[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchCondition[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[0]['type']);

        $this->indexService->simpleSearch('test', 0, 10, 'test_product', 2);
        $this->indexService->simpleSearch('test', 2, 10);
    }

    /**
     * Run advanced search
     */
    public function testAdvancedSearch()
    {
        $this->connector->expects($this->any())
            ->method('doSearch')
            ->will($this->returnValue(array('results' => array(), 'records_count' => 10)));

        $this->mapper->expects($this->once())
            ->method('getMappingConfig')
            ->will($this->returnValue($this->config));

        $select = $this->indexService->advancedSearch(
            'from (test_product, test_category)
            where name ~ "test string" and integer count = 10 and decimal price in (10, 12, 15)
            order_by name offset 10 max_results 5'
        );
        $query = $select->getQuery();
        $from = $query->getFrom();
        $searchCondition = $query->getOptions();

        $this->assertEquals('test_product', $from[0]);
        $this->assertEquals('test_category', $from[1]);

        $this->assertEquals('name', $searchCondition[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_CONTAINS, $searchCondition[0]['condition']);
        $this->assertEquals('test string', $searchCondition[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchCondition[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[0]['type']);

        $this->assertEquals('count', $searchCondition[1]['fieldName']);
        $this->assertEquals(Query::OPERATOR_EQUALS, $searchCondition[1]['condition']);
        $this->assertEquals(10, $searchCondition[1]['fieldValue']);
        $this->assertEquals(Query::TYPE_INTEGER, $searchCondition[1]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[1]['type']);

        $this->assertEquals('price', $searchCondition[2]['fieldName']);
        $this->assertEquals(Query::OPERATOR_IN, $searchCondition[2]['condition']);
        $this->assertEquals(10, $searchCondition[2]['fieldValue'][0]);
        $this->assertEquals(12, $searchCondition[2]['fieldValue'][1]);
        $this->assertEquals(15, $searchCondition[2]['fieldValue'][2]);
        $this->assertEquals(Query::TYPE_DECIMAL, $searchCondition[2]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[2]['type']);

        $this->assertEquals('name', $query->getOrderBy());
        $this->assertEquals(Query::TYPE_TEXT, $query->getOrderType());
        $this->assertEquals(Query::ORDER_ASC, $query->getOrderDirection());

        $this->assertEquals(10, $query->getFirstResult());

        $this->assertEquals(5, $query->getMaxResults());
    }
}
