<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

class AbstractMassActionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME         = 'name';
    const TEST_LABEL        = 'label';
    const TEST_ACL_RESOURCE = 'acl_resource';

    public function testGetters()
    {
        $options = array(
            'name'         => self::TEST_NAME,
            'label'        => self::TEST_LABEL,
            'acl_resource' => self::TEST_ACL_RESOURCE
        );

        /** @var \Oro\Bundle\GridBundle\Action\MassAction\AbstractMassAction $action */
        $action = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Action\MassAction\AbstractMassAction',
            array($options)
        );

        $this->assertEquals(self::TEST_NAME, $action->getName());
        $this->assertEquals(self::TEST_LABEL, $action->getLabel());
        $this->assertEquals(self::TEST_ACL_RESOURCE, $action->getAclResource());

        $this->assertNull($action->getOption('notExistedOption'));
    }
}
