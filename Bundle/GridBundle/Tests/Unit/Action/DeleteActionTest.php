<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\DeleteAction;

class DeleteActionTest extends AbstractActionTestCase
{
    /**
     * Prepare redirect action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = new DeleteAction($arguments['aclManager']);
        $this->model->setName('delete');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no option "link" for action "delete".
     */
    public function testSetOptionsError()
    {
        $this->initializeAbstractActionMock();
        $this->model->setOptions(array());
    }

    public function testGetType()
    {
        $this->initializeAbstractActionMock();
        $this->assertEquals(ActionInterface::TYPE_DELETE, $this->model->getType());
    }

    public function testSetOptions()
    {
        $this->initializeAbstractActionMock();
        $options = array('link' => '/delete_link');
        $this->model->setOptions($options);
        $this->assertEquals($options, $this->model->getOptions());
    }
}
