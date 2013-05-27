<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

class AbstractActionTest extends AbstractActionTestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';

    /**
     * @var array
     */
    protected $testOptions = array('key' => 'value');

    /**
     * Prepare abstract action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\AbstractAction', $arguments);
    }

    public function testSetName()
    {
        $this->initializeAbstractActionMock();

        $this->model->setName(self::TEST_NAME);
        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
    }

    public function testGetName()
    {
        $this->initializeAbstractActionMock();

        $this->model->setName(self::TEST_NAME);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    public function testSetAclResource()
    {
        $this->initializeAbstractActionMock();

        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertAttributeEquals(self::TEST_ACL_RESOURCE, 'aclResource', $this->model);
    }

    public function testGetAclResource()
    {
        $this->initializeAbstractActionMock();

        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertEquals(self::TEST_ACL_RESOURCE, $this->model->getAclResource());
    }

    public function testGetOptions()
    {
        $this->initializeAbstractActionMock();

        $this->model->setOptions($this->testOptions);
        $this->assertEquals($this->testOptions, $this->model->getOptions());
    }

    public function testSetOptions()
    {
        $this->initializeAbstractActionMock();

        $this->model->setOptions($this->testOptions);
        $this->assertAttributeEquals($this->testOptions, 'options', $this->model);
    }

    /**
     * Data provider for testIsGranted
     *
     * @return array
     */
    public function isGrantedDataProvider()
    {
        return array(
            'resource_granted' => array(
                '$isGranted' => true,
                '$expected'  => true,
            ),
            'resource_not_granted' => array(
                '$isGranted' => false,
                '$expected'  => false,
            ),
            'no_resource' => array(
                '$isGranted' => null,
                '$expected'  => true,
            ),
        );
    }

    /**
     * @param boolean $isGranted
     * @param boolean $expected
     * @dataProvider isGrantedDataProvider
     */
    public function testIsGranted($isGranted, $expected)
    {
        $aclManagerMock = $this->getMockForAbstractClass(
            'Oro\Bundle\UserBundle\Acl\ManagerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('isResourceGranted')
        );
        if ($isGranted !== null) {
            $aclManagerMock->expects($this->once())
                ->method('isResourceGranted')
                ->with(self::TEST_ACL_RESOURCE)
                ->will($this->returnValue($isGranted));
        }

        $this->initializeAbstractActionMock(array('aclManager' => $aclManagerMock));

        if ($isGranted !== null) {
            $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        }

        $this->assertEquals($expected, $this->model->isGranted());
    }
}
