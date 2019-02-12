<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

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

    public function setUp(): void
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(['trans'])
            ->getMockForAbstractClass();

        $this->instance = $this->getMockForAbstractClass(
            '\Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractChoiceType',
            [$this->translator]
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
        $expectedChoices = [],
        $inputChoices = []
    ) {
        // expectations for translator
        if ($expectedChoices) {
            $prefix = self::TRANSLATION_PREFIX;
            $this->translator->expects($this->exactly(count($expectedChoices)))
                ->method('trans')
                ->with($this->isType('string'), [], $expectedTranslationDomain)
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
        $actualChoices = [];
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
        return [
            'domain from options' => [
                'expectedTranslationDomain' => 'optionsDomain',
                'options'                   => ['translation_domain' => 'optionsDomain'],
                'parentTranslationDomain'   => 'parentDomain',
                'expectedChoices'           => [
                    'key1' => self::TRANSLATION_PREFIX . 'value1',
                    'key2' => self::TRANSLATION_PREFIX . 'value2',
                ],
                'inputChoices'              => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
            ],
            'domain from parent' => [
                'expectedTranslationDomain' => 'parentDomain',
                'options'                   => [],
                'parentTranslationDomain'   => 'parentDomain',
                'expectedChoices'           => ['key' => self::TRANSLATION_PREFIX . 'value'],
                'inputChoices'              => ['key' => 'value'],
            ],
            'default domain' => [
                'expectedTranslationDomain' => 'messages',
                'options'                   => [],
                'parentTranslationDomain'   => null,
                'expectedChoices'           => ['key' => self::TRANSLATION_PREFIX . 'value'],
                'inputChoices'              => ['key' => 'value'],
            ],
            'empty choices' => [
                'expectedTranslationDomain' => 'messages',
                'options'                   => [],
            ]
        ];
    }

    /**
     * Get filter form view object
     *
     * @param string|null $parentTranslationDomain
     * @param array $choices
     * @return FormView
     */
    protected function getFilterFormView($parentTranslationDomain = null, $choices = [])
    {
        $choicesFormView = new FormView();
        $choicesFormView->vars['choices'] = [];
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
