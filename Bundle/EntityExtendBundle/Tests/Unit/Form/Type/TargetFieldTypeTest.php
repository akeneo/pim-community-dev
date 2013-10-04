<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\TargetFieldType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;

class TargetFieldTypeTest extends TypeTestCase
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var  Request */
    protected $request;

    /** @var  TargetFieldType */
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityManager', 'getIds'))
            ->getMock();

        $this->request = new Request();

        $this->type = new TargetFieldType($this->configManager, $this->request);
    }

    public function testType()
    {

    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_target_field_type', $this->type->getName());
        $this->assertEquals('choice', $this->type->getParent());
    }
}
