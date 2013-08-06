<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormView;

use Oro\Bundle\EmailBundle\Form\Type\EmailTemplateSelectType;

class EmailTemplateSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTemplateSelectType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $this->type = new EmailTemplateSelectType();
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('genemu_jqueryselect2_translatable_entity', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_email_template_list', $this->type->getName());
    }

    public function testFinishView()
    {
        $optionKey = 'testKey';

        $formConfigMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $formConfigMock->expects($this->once())
            ->method('getOption')
            ->with('depends_on_parent_field')
            ->will($this->returnValue($optionKey));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfig'))
            ->getMock();
        $formMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($formConfigMock));

        $formView = new FormView();
        $this->type->finishView($formView, $formMock, array());
        $this->assertArrayHasKey('depends_on_parent_field', $formView->vars);
        $this->assertEquals($optionKey, $formView->vars['depends_on_parent_field']);
    }
}
