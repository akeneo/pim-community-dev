<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityExtendBundle\Form\Type\TargetFieldType;

class TargetFieldTypeTest extends TypeTestCase
{
    protected $configManager;
    protected $request;

    /** @var  TargetFieldType */
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->type = new TargetFieldType($this->configManager, $this->request);
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_target_field_type', $this->type->getName());
        $this->assertEquals('choice', $this->type->getParent());
    }
}
