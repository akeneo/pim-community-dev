<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;

class OptionSetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionSet
     */
    protected $optionSet;

    /**
     * @var OptionSetRelation
     */
    protected $optionRelation;

    public function setUp()
    {
        $this->optionSet      = new OptionSet();
        $this->optionRelation = new OptionSetRelation();
    }

    public function testOptionSet()
    {
        $this->checkAssertsSet($this->setSet());

        $this->assertNull($this->optionSet->getRelation());
        $this->assertEquals(1, $this->optionSet->getField());
    }

    public function testOptionSetData()
    {
        $this->optionSet->setData(null, 10, 'test', false);
        $this->checkAssertsSet($this->optionSet);
    }

    public function testOptionSetRelation()
    {
        $this->optionRelation->setData(null, 1, null, $this->setSet());
        $this->checkAssertsRelation($this->optionRelation);
    }

    protected function setSet()
    {
        $this->optionSet
            ->setId(null)
            ->setField(1)
            ->setLabel('test')
            ->setIsDefault(false)
            ->setPriority(10);

        return $this->optionSet;
    }

    protected function setRelation()
    {
        $this->optionRelation
            ->setId(null)
            ->setEntityId(1)
            ->setField(null)
            ->setOption($this->setSet());

        return $this->optionRelation;
    }

    protected function checkAssertsSet(OptionSet $entity)
    {
        $this->assertNull($entity->getId());
        $this->assertEquals('test', $entity->getLabel());
        $this->assertEquals('test', $entity->getValue());
        $this->assertFalse($entity->getIsDefault());
        $this->assertEquals(10, $entity->getPriority());
    }

    protected function checkAssertsRelation(OptionSetRelation $entity)
    {
        $this->assertNull($entity->getId());
        $this->assertNull($entity->getField());
        $this->assertEquals(1, $entity->getEntityId());
        $this->assertEquals($this->setSet(), $entity->getOption());
    }
}
