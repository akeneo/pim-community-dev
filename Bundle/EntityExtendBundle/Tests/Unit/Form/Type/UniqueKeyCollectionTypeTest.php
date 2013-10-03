<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

class UniqueKeyCollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $type;

    protected function setUp()
    {
        $this->type = new UniqueKeyCollectionType(array());
    }

    public function test()
    {
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_unique_key_collection_type', $this->type->getName());
    }
}
