<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\AjaxAction;

class AjaxActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AjaxAction
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new AjaxAction();
        $this->model->setName('ajax');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no option "link" for action "ajax".
     */
    public function testSetOptionsError()
    {
        $this->model->setOptions(array());
    }

    public function testGetType()
    {
        $this->assertEquals(ActionInterface::TYPE_AJAX, $this->model->getType());
    }

    public function testSetOptions()
    {
        $options = array(
            'link'          => '/ajax_link',
            'frontend_type' => 'ajax',
        );
        $this->model->setOptions($options);
        $this->assertEquals($options, $this->model->getOptions());
    }
}
