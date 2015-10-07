<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType;

class AbstractChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    const TRANSLATION_PREFIX = 'trans_';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var AbstractChoiceType
     */
    protected $instance;

    public function setUp()
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('trans'))
            ->getMockForAbstractClass();

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

    /**
     * @param string $expectedTranslationDomain
     * @param array $options
     * @param string|null $parentTranslationDomain
     * @param array $expectedChoices
     * @param array $inputChoices
     *
     * @dataProvider finishViewDataProvider
     */
    public function testFinishView(
        $expectedTranslationDomain,
        $options,
        $parentTranslationDomain = null,
        $expectedChoices = array(),
        $inputChoices = array()
    ) {
        // expectations for translator
        if ($expectedChoices) {
            $prefix = self::TRANSLATION_PREFIX;
            $this->translator->expects($this->exactly(count($expectedChoices)))
                ->method('trans')
                ->with($this->isType('string'), array(), $expectedTranslationDomain)
                ->will(
                    $this->returnCallback(
                        function ($id) use ($prefix) {
                            return $prefix . $id;
                        }
                    )
                );
        } else {
            $this->translator->expects($this->never())
                ->method('trans');
        }

        /** @var FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filterFormView = $this->getFilterFormView($parentTranslationDomain, $inputChoices);

        $this->instance->finishView($filterFormView, $form, $options);

        // get list of actual translated choices
        /** @var FormView $valueFormView */
        $valueFormView = $filterFormView->children['value'];
        $choiceViews = $valueFormView->vars['choices'];
        $actualChoices = array();
        /** @var ChoiceView $choiceView */
        foreach ($choiceViews as $choiceView) {
            $actualChoices[$choiceView->value] = $choiceView->label;
        }

        $this->assertEquals($expectedChoices, $actualChoices);
    }

    /**
     * @return array
     */
    public function finishViewDataProvider()
    {
        return array(
            'domain from options' => array(
                'expectedTranslationDomain' => 'optionsDomain',
                'options'                   => array('translation_domain' => 'optionsDomain'),
                'parentTranslationDomain'   => 'parentDomain',
                'expectedChoices'           => array(
                    'key1' => self::TRANSLATION_PREFIX . 'value1',
                    'key2' => self::TRANSLATION_PREFIX . 'value2',
                ),
                'inputChoices'              => array(
                    'key1' => 'value1',
                    'key2' => 'value2',
                ),
            ),
            'domain from parent' => array(
                'expectedTranslationDomain' => 'parentDomain',
                'options'                   => array(),
                'parentTranslationDomain'   => 'parentDomain',
                'expectedChoices'           => array('key' => self::TRANSLATION_PREFIX . 'value'),
                'inputChoices'              => array('key' => 'value'),
            ),
            'default domain' => array(
                'expectedTranslationDomain' => 'messages',
                'options'                   => array(),
                'parentTranslationDomain'   => null,
                'expectedChoices'           => array('key' => self::TRANSLATION_PREFIX . 'value'),
                'inputChoices'              => array('key' => 'value'),
            ),
            'empty choices' => array(
                'expectedTranslationDomain' => 'messages',
                'options'                   => array(),
            )
        );
    }

    /**
     * Get filter form view object
     *
     * @param string|null $parentTranslationDomain
     * @param array $choices
     * @return FormView
     */
    protected function getFilterFormView($parentTranslationDomain = null, $choices = array())
    {
        $choicesFormView = new FormView();
        $choicesFormView->vars['choices'] = array();
        foreach ($choices as $value => $label) {
            $choicesFormView->vars['choices'][] = new ChoiceView('someData', $value, $label);
        }

        $parentFormView = new FormView();
        $parentFormView->vars['translation_domain'] = $parentTranslationDomain;

        $filterFormView = new FormView($parentFormView);
        $filterFormView->children['value'] = $choicesFormView;

        return $filterFormView;
    }
}
