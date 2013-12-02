<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractTypeTestCase extends FormIntegrationTestCase
{
    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $defaultLocale = null;

    /**
     * @var string
     */
    protected $defaultTimezone = null;

    /**
     * @var string
     */
    private $oldLocale;

    /**
     * @var string
     */
    private $oldTimezone;

    /**
     * @var FormExtensionInterface[]
     */
    protected $formExtensions = array();

    protected function setUp()
    {
        parent::setUp();
        if ($this->defaultLocale) {
            $this->oldLocale = \Locale::getDefault();
            \Locale::setDefault($this->defaultLocale);
        }
        if ($this->defaultTimezone) {
            $this->oldTimezone = date_default_timezone_get();
            date_default_timezone_set($this->defaultTimezone);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        if ($this->defaultLocale) {
            \Locale::setDefault($this->oldLocale);
        }
        if ($this->defaultTimezone) {
            date_default_timezone_set($this->oldTimezone);
        }
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockTranslator()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->anything(), array())
            ->will($this->returnArgument(0));

        return $translator;
    }

    /**
     * @return OptionsResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockOptionsResolver()
    {
        return $this->getMockForAbstractClass('Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }

    /**
     * @dataProvider setDefaultOptionsDataProvider
     * @param array $defaultOptions
     * @param array $requiredOptions
     */
    public function testSetDefaultOptions(array $defaultOptions, array $requiredOptions = array())
    {
        $resolver = $this->createMockOptionsResolver();

        if ($defaultOptions) {
            $resolver->expects($this->once())->method('setDefaults')->with($defaultOptions)->will($this->returnSelf());
        }

        if ($requiredOptions) {
            $resolver->expects($this->once())->method('setRequired')->with($requiredOptions)->will($this->returnSelf());
        }

        $this->getTestFormType()->setDefaultOptions($resolver);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public function setDefaultOptionsDataProvider();

    /**
     * @dataProvider bindDataProvider
     * @param array $bindData
     * @param array $formData
     * @param array $viewData
     * @param array $customOptions
     */
    public function testBindData(
        array $bindData,
        array $formData,
        array $viewData,
        array $customOptions = array()
    ) {
        $form = $this->factory->create($this->getTestFormType(), null, $customOptions);

        $form->submit($bindData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();

        foreach ($viewData as $key => $value) {
            $this->assertArrayHasKey($key, $view->vars);
            $this->assertEquals($value, $view->vars[$key]);
        }
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public function bindDataProvider();

    /**
     * @return FormTypeInterface
     */
    abstract protected function getTestFormType();

    /**
     * @return array|FormExtensionInterface[]
     */
    protected function getExtensions()
    {
        return $this->formExtensions;
    }
}
