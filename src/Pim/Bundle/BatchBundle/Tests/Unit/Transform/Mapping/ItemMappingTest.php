<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Model\Mapping;

use Pim\Bundle\BatchBundle\Transform\Mapping\FieldMapping;
use Pim\Bundle\BatchBundle\Transform\Mapping\ItemMapping;

/**
 * Test related class
 *
 *
 */
class ItemMappingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ItemMapping
     */
    protected $item;

    /**
     * Create an Item Mapping
     *
     * @return \Pim\Bundle\BatchBundle\Model\Mapping\ItemMapping
     */
    protected function createItemMapping()
    {
        $this->item = new ItemMapping();

        return $this->item;
    }

    /**
     * Test related method
     */
    public function testAddField()
    {
        // instanciate
        $this->createItemMapping()->add('test-source', 'test-destination', true);

        // assert fields
        $fields = $this->item->getFields();
        $this->assertCount(1, $fields);

        // assert field
        $field = $fields[0];
        $this->assertEquals('test-source', $field->getSource());
        $this->assertEquals('test-destination', $field->getDestination());
        $this->assertTrue($field->getIsIdentifier());
    }

    /**
     * Test mapping functionnalities
     */
    public function testMapping()
    {
        $this->createItemMapping();

        $this->item->add('attribute_code', 'code', true);
        $this->item->add('name', 'name');

        $fields = $this->item->getFields();
        $this->assertCount(2, $fields);
    }
}
