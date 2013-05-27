<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\Query
     */
    private $query;

    public function setUp()
    {
        $this->query = new Query();
    }

    public function testEntityId()
    {
        $this->assertNull($this->query->getEntity());
        $this->query->setEntity('test_entity');
        $this->assertEquals('test_entity', $this->query->getEntity());
    }

    public function testQuery()
    {
        $this->assertNull($this->query->getQuery());
        $this->query->setQuery('test_query');
        $this->assertEquals('test_query', $this->query->getQuery());
    }

    public function testResultCount()
    {
        $this->assertNull($this->query->getResultCount());
        $this->query->setResultCount(10);
        $this->assertEquals(10, $this->query->getResultCount());
    }

    public function testCreatedAt()
    {
        $this->assertNull($this->query->getCreatedAt());
        $this->query->setCreatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->query->getCreatedAt()->format('Y-m-d'));
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->query->getCreatedAt());
        $this->query->beforeSave();
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($currentDate->format('Y-m-d'), $this->query->getCreatedAt()->format('Y-m-d'));
    }

    public function testGetId()
    {
        $this->assertNull($this->query->getId());
    }
}
