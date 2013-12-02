<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Symfony\Component\Form\FormView;

class TooltipFormExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->getMock();
        $resolver->expects($this->once())
            ->method('setOptional')
            ->with(
                array(
                    'tooltip',
                    'tooltip_details_enabled',
                    'tooltip_details_anchor',
                    'tooltip_details_link',
                    'tooltip_placement'
                )
            );

        $extension = new TooltipFormExtension();
        $extension->setDefaultOptions($resolver);
        $this->assertEquals('form', $extension->getExtendedType());
    }

    public function testBuildView()
    {
        $options = array(
            'tooltip' => 'test',
            'tooltip_details_enabled' => true,
            'tooltip_details_anchor' => 'test',
            'tooltip_details_link' => 'test',
            'tooltip_placement' => 'test'
        );
        $view = new FormView();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $extension = new TooltipFormExtension();
        $extension->buildView($view, $form, $options);

        foreach ($options as $option => $value) {
            $this->assertArrayHasKey($option, $view->vars);
            $this->assertEquals($options[$option], $view->vars[$option]);
        }
    }
}
