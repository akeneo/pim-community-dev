<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;

class OptionSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionSet
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new OptionSet();
    }

    public function testGettersSetters()
    {
        $entity = $this->entity;
        $entity
            ->setId(null)
            ->setField(1)
            ->setLabel('test')
            ->setIsDefault(false)
            ->setPriority(10);

        $this->checkAsserts($entity);

        $this->assertNull($entity->getRelation());
        $this->assertEquals(1, $entity->getField());
    }

    public function testSetData()
    {
        $entity = $this->entity;
        $entity->setData(null, 10, 'test', false);

        $this->checkAsserts($entity);
    }

    protected function checkAsserts($entity)
    {
        $this->assertNull($entity->getId());
        $this->assertEquals('test', $entity->getLabel());
        $this->assertEquals('test', $entity->getValue());
        $this->assertFalse($entity->getIsDefault());
        $this->assertEquals(10, $entity->getPriority());
    }
}
