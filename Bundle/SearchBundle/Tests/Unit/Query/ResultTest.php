<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Query;

use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;

use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Query\Query;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Query\Result
     */
    private $result;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroTestBundle:test'))
            ->will($this->returnValue($this->repository));

        $this->product = new Product();
        $this->product->setName('test product');

        $this->repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->product));

        $items[] = new Item(
            $this->om,
            'OroTestBundle:test',
            1,
            'test title',
            'http://example.com',
            'test text',
            array(
                 'alias' => 'test_product',
                 'label' => 'test product',
                 'fields' => array(
                     array(
                         'name'          => 'name',
                         'target_type'   => 'text',
                     ),
                 ),
            )
        );
        $items[] = new Item(
            $this->om,
            'OroTestBundle:test',
            2,
            'test title 2',
            'http://example.com',
            'test text',
            array(
                 'alias' => 'test_product',
                 'label' => 'test product',
                 'fields' => array(
                     array(
                         'name'          => 'name',
                         'target_type'   => 'text',
                     ),
                 ),
            )
        );
        $items[] = new Item(
            $this->om,
            'OroTestBundle:test',
            3,
            'test title 3',
            'http://example.com',
            'test text',
            array(
                 'alias' => 'test_product',
                 'label' => 'test product',
                 'fields' => array(
                     array(
                         'name'          => 'name',
                         'target_type'   => 'text',
                     ),
                 ),
            )
        );

        $query = new Query();
        $query->createQuery(Query::SELECT)
            ->from(array('OroTestBundle:test', 'OroTestBundle:product'))
            ->andWhere('name', '~', 'test string', Query::TYPE_TEXT);

        $this->result = new Result($query, $items, 3);
        $this->result1 = new Result($query, array(), 0);
    }

    public function testGetQuery()
    {
        $query = $this->result->getQuery();
        $from = $query->getFrom();
        $searchCondition = $query->getOptions();

        $this->assertEquals('OroTestBundle:test', $from[0]);
        $this->assertEquals('OroTestBundle:product', $from[1]);

        $this->assertEquals('name', $searchCondition[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_CONTAINS, $searchCondition[0]['condition']);
        $this->assertEquals('test string', $searchCondition[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchCondition[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchCondition[0]['type']);
    }

    public function testGetRecordsCount()
    {
        $this->assertEquals(3, $this->result->getRecordsCount());
    }

    public function testToSearchResultData()
    {
        $resultArray = $this->result->toSearchResultData();
        $this->assertEquals(3, $resultArray['records_count']);
        $this->assertEquals(3, $resultArray['count']);
        $this->assertEquals('OroTestBundle:test', $resultArray['data'][0]['entity_name']);
        $this->assertEquals(2, $resultArray['data'][1]['record_id']);
        $this->assertEquals('test title 3', $resultArray['data'][2]['record_string']);

        $this->result1->toSearchResultData();
    }
}
