<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\AbstractAction;

class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';

    /**
     * @var AbstractAction
     */
    protected $model;

    /**
     * @var array
     */
    protected $testOptions = array('key' => 'value');

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\AbstractAction');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetName()
    {
        $this->model->setName(self::TEST_NAME);
        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
    }

    public function testGetName()
    {
        $this->model->setName(self::TEST_NAME);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    public function testSetAclResource()
    {
        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertAttributeEquals(self::TEST_ACL_RESOURCE, 'aclResource', $this->model);
    }

    public function testGetAclResource()
    {
        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertEquals(self::TEST_ACL_RESOURCE, $this->model->getAclResource());
    }

    public function testGetOptions()
    {
        $this->model->setOptions($this->testOptions);
        $this->assertEquals($this->testOptions, $this->model->getOptions());
    }

    public function testSetOptions()
    {
        $this->model->setOptions($this->testOptions);
        $this->assertAttributeEquals($this->testOptions, 'options', $this->model);
    }
}
