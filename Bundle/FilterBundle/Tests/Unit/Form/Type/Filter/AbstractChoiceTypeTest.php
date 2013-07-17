<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class AbstractChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var \Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType
     */
    protected $instance;

    public function setUp()
    {
        $this->translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $this->instance = $this->getMockForAbstractClass(
            '\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType',
            array($this->translator)
        );
    }

    public function tearDown()
    {
        unset($this->translator);
        unset($this->instance);
    }

    public function testFinishViewEmptyValues()
    {
        // empty values form view
        $this->translator->expects($this->never())
            ->method('trans');

        $formMock = $this->getFormMock();
        $formViewMock = $this->getMock('Symfony\Component\Form\FormView');
        $this->instance->finishView($formViewMock, $formMock, array());

        // empty choice list
        $choicesFormViewMock = $this->getFormViewMock();
        $choicesFormViewMock->vars['choices'] = array();
        $formViewMock->children['value'] = $choicesFormViewMock;
        $this->instance->finishView($formViewMock, $formMock, array());
    }

    /**
     * @dataProvider provider
     * @param $expectedDomain
     * @param $options
     * @param $parentTranslationDomain
     */
    public function testFinishView($expectedDomain, $options, $parentTranslationDomain)
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('testLabel', array(), $expectedDomain);

        $formMock = $this->getFormMock();
        $formViewMock = $this->getFormViewMock($parentTranslationDomain);

        $this->instance->finishView($formViewMock, $formMock, $options);
    }

    /**
     * Returns data
     *
     * @return array
     */
    public function provider()
    {
        return array(
            'domain from options' => array(
                'optionsDomain',
                array('translation_domain' => 'optionsDomain'),
                null
            ),
            'domain from parent' => array(
                'parentDomain',
                array('translation_domain' => null),
                'parentDomain'
            ),
            'default domain' => array(
                'messages',
                array('translation_domain' => null),
                null
            )
        );
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
     * @param string|null $parentTranslationDomain
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormViewMock($parentTranslationDomain = null)
    {
        $formViewMock = $this->getMock('Symfony\Component\Form\FormView');

        $parentFormViewMock = $this->getMock('Symfony\Component\Form\FormView');
        $parentFormViewMock->vars['translation_domain'] = '';

        $choiceOneFormViewMock = new ChoiceView('testData', 'testValue', 'testLabel');
        $choicesFormViewMock = $this->getMock('Symfony\Component\Form\FormView');
        $choicesFormViewMock->vars['choices'] = array($choiceOneFormViewMock);

        $formViewMock->children['value'] = $choicesFormViewMock;
        $formViewMock->parent = $parentFormViewMock;
        $formViewMock->parent->vars['translation_domain'] = $parentTranslationDomain;

        return $formViewMock;
    }
}
