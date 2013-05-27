<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\RedirectAction;

class RedirectActionTest extends AbstractActionTestCase
{
    /**
     * Prepare redirect action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = new RedirectAction($arguments['aclManager']);
        $this->model->setName('redirect');
    }

    public function testGetType()
    {
        $this->initializeAbstractActionMock();
        $this->assertEquals(ActionInterface::TYPE_REDIRECT, $this->model->getType());
    }

    public function testSetOptions()
    {
        $this->initializeAbstractActionMock();
        $options = array(
            'link' => '/redirect_link',
            'backUrl' => true,
            'backUrlParameter' => 'backurl'
        );
        $this->model->setOptions($options);
        $this->assertEquals($options, $this->model->getOptions());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no option "link" for action "redirect".
     */
    public function testSetOptionsError()
    {
        $this->initializeAbstractActionMock();
        $this->model->setOptions(array());
    }
}
