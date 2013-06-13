<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\SearchIndexer;

use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchIndexer\IndexerSearchHandler;

class IndexerSearchHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SEARCH_STRING = 'test_search_string';
    const TEST_FIRST_RESULT  = 30;
    const TEST_MAX_RESULTS   = 10;
    const TEST_ENTITY_ALIAS  = 'test_entity_alias';

    /**
     * @var array
     */
    protected $testElements = array('test', 'elements', 'array');

    /**
     * @var array
     */
    protected $testResult = array('test', 'result', 'data');

    public function testSearch()
    {
        // mocks
        $result = new Result(new Query(), $this->testElements);

        $indexer = $this->getMock(
            'Oro\Bundle\SearchBundle\Engine\Indexer',
            array('simpleSearch'),
            array(),
            '',
            false
        );
        $indexer->expects($this->once())
            ->method('simpleSearch')
            ->with(self::TEST_SEARCH_STRING, self::TEST_FIRST_RESULT, self::TEST_MAX_RESULTS, self::TEST_ENTITY_ALIAS)
            ->will($this->returnValue($result));

        $resultFormatter = $this->getMock(
            'Oro\Bundle\SearchBundle\Formatter\ResultFormatter',
            array('getOrderedResultEntities'),
            array(),
            '',
            false
        );
        $resultFormatter->expects($this->once())
            ->method('getOrderedResultEntities')
            ->with($this->testElements)
            ->will($this->returnValue($this->testResult));

        // test
        $searchHandler = new IndexerSearchHandler($indexer, $resultFormatter, self::TEST_ENTITY_ALIAS);
        $result = $searchHandler->search(self::TEST_SEARCH_STRING, self::TEST_FIRST_RESULT, self::TEST_MAX_RESULTS);

        $this->assertEquals($this->testResult, $result);
    }
}
