<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyType;

class UniqueKeyTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    protected function setUp()
    {
        $this->type = new UniqueKeyType(array());
    }

    public function test()
    {
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_unique_key_type', $this->type->getName());
    }
}
