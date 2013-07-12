<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FormBundle\DependencyInjection\Compiler\FormCompilerPass;

class FormCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $sourceResources
     * @param array $expectedResources
     *
     * @dataProvider processDataProvider
     */
    public function testProcess(array $sourceResources, array $expectedResources = null)
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter', 'setParameter'))
            ->getMockForAbstractClass();
        $container->expects($this->once())
            ->method('getParameter')
            ->with('twig.form.resources')
            ->will($this->returnValue($sourceResources));
        if ($expectedResources !== null) {
            $container->expects($this->any())
                ->method('setParameter')
                ->with('twig.form.resources', $expectedResources);
        }

        $compiler = new FormCompilerPass();
        $compiler->process($container);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            'no form layout' => array(
                'sourceResources' => array(
                    'DemoBundle:Form:first_layout.html.twig',
                    'DemoBundle:Form:second_layout.html.twig',
                ),
            ),
            'with form layout' => array(
                'sourceResources' => array(
                    'DemoBundle:Form:first_layout.html.twig',
                    FormCompilerPass::ORO_FORM_LAYOUT,
                    'DemoBundle:Form:second_layout.html.twig',
                    FormCompilerPass::GENEMU_LAYOUT_PREFIX . 'some_layout.html.twig',
                ),
                'expectedResources' => array(
                    'DemoBundle:Form:first_layout.html.twig',
                    FormCompilerPass::GENEMU_LAYOUT_PREFIX . 'some_layout.html.twig',
                    FormCompilerPass::ORO_FORM_LAYOUT,
                    'DemoBundle:Form:second_layout.html.twig',
                ),
            ),
        );
    }
}
