<?php
namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\EntityType;

class EntityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  EntityType */
    protected $type;

    public function setUp()
    {
        $this->type = new EntityType();
    }

    public function testBuildForm()
    {
    }

    public function testOptions()
    {

    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_entity_type', $this->type->getName());
    }
}
