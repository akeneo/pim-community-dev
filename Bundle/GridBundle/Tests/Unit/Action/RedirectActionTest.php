<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\RedirectAction;

class RedirectActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RedirectAction
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new RedirectAction();
        $this->model->setName('redirect');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetType()
    {
        $this->assertEquals(ActionInterface::TYPE_REDIRECT, $this->model->getType());
    }

    public function testSetOptions()
    {
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
        $this->model->setOptions(array());
    }
}
