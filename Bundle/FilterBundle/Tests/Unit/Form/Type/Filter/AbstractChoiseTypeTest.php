<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class AbstractChoiseTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFinishViewEmptyForm()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->never())
            ->method('trans');

        $instance = $this->getMockForAbstractClass('\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType', array($translator));

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock();
        $instance->finishView($formViewMock, $formMock, array());
    }

    public function testFinishViewEmptyChoicesList()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->never())
            ->method('trans');

        $instance = $this->getMockForAbstractClass('\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType', array($translator));

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock();

        $choicesFormViewMock = $this->getFormViewMock();
        $choicesFormViewMock->vars['choices'] = array();
        $formViewMock->children['value'] = $choicesFormViewMock;

        $instance->finishView($formViewMock, $formMock, array());
    }

    public function testFinishViewDefaultDomain()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())
            ->method('trans')
            ->with('testLabel', array(), 'messages');

        $instance = $this->getMockForAbstractClass('\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType', array($translator));

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock();
        $parentFormViewMock = $this->getFormViewMock();
        $parentFormViewMock->vars['translation_domain'] = '';

        $choiceOneFormViewMock = new ChoiceView('testData', 'testValue', 'testLabel');

        $choicesFormViewMock = $this->getFormViewMock();
        $choicesFormViewMock->vars['choices'] = array($choiceOneFormViewMock);
        $formViewMock->children['value'] = $choicesFormViewMock;
        $formViewMock->parent = $parentFormViewMock;

        $instance->finishView($formViewMock, $formMock, array('translation_domain' => ''));
    }

    public function testFinishViewDomainFromParent()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())
            ->method('trans')
            ->with('testLabel', array(), 'parentDomain');

        $instance = $this->getMockForAbstractClass('\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType', array($translator));

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock();
        $parentFormViewMock = $this->getFormViewMock();
        $parentFormViewMock->vars['translation_domain'] = 'parentDomain';

        $choiceOneFormViewMock = new ChoiceView('testData', 'testValue', 'testLabel');

        $choicesFormViewMock = $this->getFormViewMock();
        $choicesFormViewMock->vars['choices'] = array($choiceOneFormViewMock);
        $formViewMock->children['value'] = $choicesFormViewMock;
        $formViewMock->parent = $parentFormViewMock;

        $instance->finishView($formViewMock, $formMock, array('translation_domain' => ''));
    }

    public function testFinishViewDomainFromOptions()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->once())
            ->method('trans')
            ->with('testLabel', array(), 'optionsDomain');

        $instance = $this->getMockForAbstractClass('\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType', array($translator));

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock();
        $parentFormViewMock = $this->getFormViewMock();
        $parentFormViewMock->vars['translation_domain'] = '';

        $choiceOneFormViewMock = new ChoiceView('testData', 'testValue', 'testLabel');

        $choicesFormViewMock = $this->getFormViewMock();
        $choicesFormViewMock->vars['choices'] = array($choiceOneFormViewMock);
        $formViewMock->children['value'] = $choicesFormViewMock;
        $formViewMock->parent = $parentFormViewMock;

        $instance->finishView($formViewMock, $formMock, array('translation_domain' => 'optionsDomain'));
    }

    /**
     * Get form object mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormMock()
    {
        return $this->getMock('Symfony\Component\Form\Test\FormInterface');
    }

    /**
     * Get form view mock object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormViewMock()
    {
        return $this->getMock('Symfony\Component\Form\FormView');
    }
}
