<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistry;

class SearchRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchHandler;

    /**
     * @var SearchRegistry
     */
    protected $searchRegistry;

    protected function setUp()
    {
        $this->searchHandler = $this->getMock('Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface');
        $this->searchRegistry = new SearchRegistry();
    }

    public function testAddSearchHandler()
    {
        $this->searchRegistry->addSearchHandler('test', $this->searchHandler);
        $this->assertAttributeSame(
            array('test' => $this->searchHandler),
            'searchHandlers',
            $this->searchRegistry
        );
    }

    public function testGetAndHasSearchHandler()
    {
        $this->searchRegistry->addSearchHandler('test', $this->searchHandler);

        $this->assertTrue($this->searchRegistry->hasSearchHandler('test'));
        $this->assertFalse($this->searchRegistry->hasSearchHandler('testNotExists'));

        $this->assertSame($this->searchHandler, $this->searchRegistry->getSearchHandler('test'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Search handler "test" is not registered
     */
    public function testGetSearchHandlerFails()
    {
        $this->searchRegistry->getSearchHandler('test');
    }
}
