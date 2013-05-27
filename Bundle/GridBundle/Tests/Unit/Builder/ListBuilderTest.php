<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Builder\ORM;

use Oro\Bundle\GridBundle\Builder\ListBuilder;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class ListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME = 'test_name';
    const TEST_TYPE = 'test_type';

    /**
     * @var ListBuilder
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new ListBuilder();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetBaseList()
    {
        $collection = $this->model->getBaseList();

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection', $collection);
        $this->assertEmpty($collection->getElements());
    }

    public function testBuildField()
    {
        $fieldDescription = new FieldDescription();

        $this->assertNull($fieldDescription->getType());
        $this->model->buildField(self::TEST_TYPE, $fieldDescription);
        $this->assertEquals(self::TEST_TYPE, $fieldDescription->getType());
    }

    public function testAddField()
    {
        $collection       = new FieldDescriptionCollection();
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName(self::TEST_NAME);

        $this->model->addField($collection, self::TEST_TYPE, $fieldDescription);

        $this->assertEquals(self::TEST_TYPE, $fieldDescription->getType());
        $this->assertEquals($fieldDescription, $collection->get(self::TEST_NAME));
    }
}
