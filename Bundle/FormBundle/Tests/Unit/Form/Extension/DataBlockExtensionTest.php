<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\FormView;

class DataBlockExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  DataBlockExtension */
    private $formExtension;

    private $securityFacade;

    private $options = array('block' => 1, 'subblock' => 1, 'block_config' => 1, 'tooltip' => 1);

    public function setUp()
    {
        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()->getMock();
        $this->formExtension = new DataBlockExtension($this->securityFacade);
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolver $resolver */
        $resolver = new OptionsResolver();
        $this->formExtension->setDefaultOptions($resolver);

        $this->assertEquals(
            $this->options,
            $this->readAttribute($resolver, 'knownOptions')
        );
    }

    public function testGetExtendedType()
    {
        $this->assertEquals('form', $this->formExtension->getExtendedType());
    }

    public function testBuildView()
    {
        /** @var FormView $formView */
        $formView = new FormView();

        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formExtension->buildView($formView, $form, $this->options);

        $this->assertEquals($this->options['block'], $formView->vars['block']);
        $this->assertEquals($this->options['subblock'], $formView->vars['subblock']);
        $this->assertEquals($this->options['block_config'], $formView->vars['block_config']);
        $this->assertEquals($this->options['tooltip'], $formView->vars['tooltip']);
    }
}
