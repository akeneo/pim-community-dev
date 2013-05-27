<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridView;
use Symfony\Component\Form\FormView;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class DatagridViewTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDatagrid()
    {
        $datagrid = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $datagridView = new DatagridView($datagrid);

        $this->assertEquals($datagrid, $datagridView->getDatagrid());
    }

    public function testGetFormView()
    {
        $formView = new FormView();

        $form = $this->getMock('Symfony\Component\Form\Form', array('createView'), array(), '', false);
        $form->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($formView));

        $datagrid = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getForm')
        );
        $datagrid->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $datagridView = new DatagridView($datagrid);
        $this->assertEquals($formView, $datagridView->getFormView());
        // getters must be called only once and must return the same form view
        $this->assertEquals($formView, $datagridView->getFormView());
    }
}
