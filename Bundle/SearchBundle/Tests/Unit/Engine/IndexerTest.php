<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Engine;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Indexer
     */
    protected $indexService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityProvider;

    /**
     * @var array
     */
    protected $config = array(
        'Oro\Bundle\DataBundle\Entity\Product' => array(
            'alias' => 'test_product',
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
        ),
        'Oro\Bundle\DataBundle\Entity\Customer' => array(
            'alias' => 'test_customer',
            'label' => 'test customer',
            'fields' => array(),
        ),
    );

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\AbstractEngine')
            ->disableOriginalConstructor()
            ->setMethods(array('search'))
            ->getMockForAbstractClass();

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->setMethods(array('getMappingConfig', 'getEntitiesListAliases'))
            ->getMock();
        $this->mapper->expects($this->any())
            ->method('getMappingConfig')
            ->will($this->returnValue($this->config));
        $config = $this->config;
        $this->mapper->expects($this->any())
            ->method('getEntitiesListAliases')
            ->will(
                $this->returnCallback(
                    function () use ($config) {
                        $aliases = array();
                        foreach ($config as $entityName => $entityOptions) {
                            if (!empty($entityOptions['alias'])) {
                                $aliases[$entityName] = $entityOptions['alias'];
                            }
                        }
                        return $aliases;
                    }
                )
            );

        $this->securityProvider = $this->getMockBuilder('Oro\Bundle\SearchBundle\Security\SecurityProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityProvider->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $this->securityProvider->expects($this->any())
            ->method('isProtectedEntity')
            ->will($this->returnValue(true));

        $this->indexService = new Indexer(
            $this->entityManager,
            $this->adapter,
            $this->mapper,
            $this->securityProvider
        );
    }

    public function testSelect()
    {
        $query = $this->indexService->select();

        $this->assertAttributeEquals($this->entityManager, 'em', $query);
        $this->assertEquals($this->config, $query->getMappingConfig());
        $this->assertEquals('select', $query->getQuery());
    }

    public function testQuery()
    {
        $select = $this->indexService->select();

        $resultItem = new Item($this->entityManager);
        $searchResults = array($resultItem);

        $this->adapter->expects($this->once())
            ->method('search')
            ->will(
                $this->returnCallback(
                    function (Query $query) use ($searchResults) {
                        return new Result($query, $searchResults, count($searchResults));
                    }
                )
            );

        $result = $this->indexService->query($select);
        $this->assertEquals($searchResults, $result->getElements());
        $this->assertEquals(count($searchResults), $result->getRecordsCount());
    }

    /**
     * @return array
     */
    public function simpleSearchDataProvider()
    {
        return array(
            'no extra parameters' => array(
                'expectedQuery'
                    => 'select from test_product, test_customer where and((text)all_text ~ qwerty)',
                'string' => 'qwerty',
            ),
            'custom offset' => array(
                'expectedQuery'
                    => 'select from test_product, test_customer where and((text)all_text ~ qwerty) offset 10',
                'string' => 'qwerty',
                'offset' => 10,
            ),
            'custom offset custom maxResults' => array(
                'expectedQuery'
                    => 'select from test_product, test_customer where and((text)all_text ~ qwerty) limit 200 offset 10',
                'string' => 'qwerty',
                'offset' => 10,
                'maxResults' => 200,
            ),
            'custom from' => array(
                'expectedQuery'
                    => 'select from test_customer where and((text)all_text ~ qwerty)',
                'string' => 'qwerty',
                'offset' => 0,
                'maxResults' => 0,
                'from' => 'test_customer',
            ),
            'all custom parameters' => array(
                'expectedQuery'
                    => 'select from test_customer where and((text)all_text ~ qwerty) limit 200 offset 400',
                'string' => 'qwerty',
                'offset' => 10,
                'maxResults' => 200,
                'from' => 'test_customer',
                'page' => 3,
            ),
            'unknown from' => array(
                'expectedQuery'
                    => 'select where and((text)all_text ~ qwerty)',
                'string' => 'qwerty',
                'offset' => 0,
                'maxResults' => 0,
                'from' => 'unknown_entity',
            ),
        );
    }

    /**
     * @param string $expectedQuery
     * @param string $string
     * @param int $offset
     * @param int $maxResults
     * @param null $from
     * @param int $page
     * @dataProvider simpleSearchDataProvider
     */
    public function testSimpleSearch($expectedQuery, $string, $offset = 0, $maxResults = 0, $from = null, $page = 0)
    {
        $searchResults = array('one', 'two', 'three');

        $this->adapter->expects($this->any())
            ->method('search')
            ->will(
                $this->returnCallback(
                    function (Query $query) use ($searchResults) {
                        return new Result($query, $searchResults, count($searchResults));
                    }
                )
            );

        $result = $this->indexService->simpleSearch($string, $offset, $maxResults, $from, $page);
        $actualQuery = $this->combineQueryString($result->getQuery());

        if ($result->getQuery()->getFrom()) {
            $this->assertEquals($searchResults, $result->getElements());
            $this->assertEquals(count($searchResults), $result->getRecordsCount());
        } else {
            $this->assertEmpty($result->getElements());
            $this->assertEquals(0, $result->getRecordsCount());
        }
        $this->assertEquals($actualQuery, $expectedQuery);
    }

    public function testAdvancedSearch()
    {
        $searchResults = array('one', 'two', 'three');

        $this->adapter->expects($this->any())
            ->method('search')
            ->will(
                $this->returnCallback(
                    function (Query $query) use ($searchResults) {
                        return new Result($query, $searchResults, count($searchResults));
                    }
                )
            );

        $sourceQuery = 'from (test_product, test_category)' .
            ' where name ~ "test string" and integer count = 10 and decimal price in (10, 12, 15)' .
            ' order_by name offset 10 max_results 5';
        $expectedQuery = 'select from test_product' .
            ' where and((text)name ~ test string) and((integer)count = 10) and((decimal)price in (10, 12, 15))' .
            ' order by name asc limit 5 offset 10';

        $result = $this->indexService->advancedSearch($sourceQuery);
        $actualQuery = $this->combineQueryString($result->getQuery());

        $this->assertEquals($searchResults, $result->getElements());
        $this->assertEquals(count($searchResults), $result->getRecordsCount());
        $this->assertEquals($expectedQuery, $actualQuery);
    }

    /**
     * @param Query $query
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function combineQueryString(Query $query)
    {
        $selectString = $query->getQuery();

        $fromString = '';
        if ($query->getFrom()) {
            $fromString .=  ' from ' . implode(', ', $query->getFrom());
        }

        $whereParts = array();
        foreach ($query->getOptions() as $whereOptions) {
            if (is_array($whereOptions['fieldValue'])) {
                $whereOptions['fieldValue'] = '(' . implode(', ', $whereOptions['fieldValue']) . ')';
            }
            $whereParts[] = sprintf(
                '%s((%s)%s %s %s)',
                $whereOptions['type'],
                $whereOptions['fieldType'],
                $whereOptions['fieldName'],
                $whereOptions['condition'],
                $whereOptions['fieldValue']
            );
        }
        $whereString = '';
        if ($whereParts) {
            $whereString .= ' where ' . implode(' ', $whereParts);
        }

        $orderByString = '';
        if ($query->getOrderBy()) {
            $orderByString .= ' ' . $query->getOrderBy();
        }
        if ($query->getOrderDirection()) {
            $orderByString .= ' ' . $query->getOrderDirection();
        }
        if ($orderByString) {
            $orderByString = ' order by' . $orderByString;
        }

        $limitString = '';
        if ($query->getMaxResults() && $query->getMaxResults() != Query::INFINITY) {
            $limitString = ' limit ' . $query->getMaxResults();
        }

        $offsetString = '';
        if ($query->getFirstResult()) {
            $offsetString .= ' offset ' . $query->getFirstResult();
        }

        return $selectString . $fromString. $whereString . $orderByString . $limitString . $offsetString;
    }
}
