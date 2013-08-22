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

    /**
     * @param array $toSet
     * @param array $expected
     * @dataProvider optionsProvider
     */
    public function testSetOptions($toSet, $expected)
    {
        $this->model->setOptions($toSet);
        $this->assertEquals($expected, $this->model->getOptions());
    }

    /**
     * @return array
     */
    public function optionsProvider()
    {
        return array(
            'options equals to provided' => array(
                'to set' => array(
                    'link'         => '/delete_link',
                    'confirmation' => true,
                ),
                'expected from get' => array(
                    'link'         => '/delete_link',
                    'confirmation' => true,
                ),
            ),
            'option confirmation is true by default' => array(
                'to set' => array(
                    'link'         => '/delete_link',
                ),
                'expected from get' => array(
                    'link'         => '/delete_link',
                    'confirmation' => true,
                ),
            )
        );
    }
}
