<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\DeleteAction;

class DeleteActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeleteAction
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new DeleteAction();
        $this->model->setName('delete');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no option "link" for action "delete".
     */
    public function testSetOptionsError()
    {
        $this->model->setOptions(array());
    }

    public function testGetType()
    {
        $this->assertEquals(ActionInterface::TYPE_DELETE, $this->model->getType());
    }

    public function testSetOptions()
    {
        $options = array('link' => '/delete_link');
        $this->model->setOptions($options);
        $this->assertEquals($options, $this->model->getOptions());
    }
}
