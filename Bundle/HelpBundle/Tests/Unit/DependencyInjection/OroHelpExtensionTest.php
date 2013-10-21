<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\HelpBundle\DependencyInjection\OroHelpExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class OroHelpExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroHelpExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new OroHelpExtension();
    }

    public function testLoadServices()
    {
        $container = $this->createContainer();

        $this->extension->load(
            array(
                'oro_help' => array(
                    'defaults' => array(
                        'server' => 'http://server.com'
                    )
                )
            ),
            $container
        );

        $this->assertTrue($container->hasDefinition('oro_help.model.help_link_provider'));
        $this->assertTrue($container->hasDefinition('oro_help.twig.extension'));
        $twigExtension = $container->getDefinition('oro_help.twig.extension');
        $this->assertTrue($twigExtension->hasTag('twig.extension'));
    }

    /**
     * @dataProvider loadConfigurationDataProvider
     */
    public function testLoadConfiguration(array $configs, array $bundles, array $expectedConfiguration)
    {
        $container = $this->createContainer($bundles);

        $this->extension->load(
            $configs,
            $container
        );

        $this->assertTrue($container->hasDefinition('oro_help.model.help_link_provider'));
        $linkProvider = $container->getDefinition('oro_help.model.help_link_provider');
        $this->assertEquals(
            array(
                array(
                    'setConfiguration',
                    array($expectedConfiguration)
                )
            ),
            $linkProvider->getMethodCalls()
        );
    }

    public function loadConfigurationDataProvider()
    {
        return array(
            'without_bundles' => array(
                'configs' => array(
                    'oro_help' => array(
                        'defaults' => array(
                            'server' => 'http://server.com'
                        )
                    )
                ),
                'bundles' => array(),
                'expectedConfiguration' => array(
                    'defaults' => array(
                        'server' => 'http://server.com',
                    ),
                    'vendors' => array(),
                    'resources' => array(),
                    'routes' => array(),
                )
            ),
            'with_bundles' => array(
                'configs' => array(
                    'oro_help' => array(
                        'defaults' => array(
                            'server' => 'http://server.com'
                        )
                    )
                ),
                'bundles' => array(
                    'Oro\Bundle\HelpBundle\Tests\Unit\DependencyInjection\Fixtures\BarBundle\BarBundle',
                    'Oro\Bundle\HelpBundle\Tests\Unit\DependencyInjection\Fixtures\FooBundle\FooBundle',
                ),
                'expectedConfiguration' => array(
                    'defaults' => array(
                        'server' => 'http://server.com',
                    ),
                    'vendors' => array(
                        'Bar' => array(
                            'alias' => 'BarAliasOverride'
                        ),
                        'Foo' => array(
                            'alias' => 'FooAlias'
                        ),
                    ),
                    'resources' => array(
                        'FooBundle' => array(
                            'alias' => 'Foo'
                        ),
                        'BarBundle' => array(
                            'alias' => 'BarOverride'
                        ),
                        'BarBundle:Bar' => array(
                            'alias' => 'BarBundleBar'
                        ),
                        'BarBundle:Bar:foo' => array(
                            'server' => 'http://server.com/',
                            'prefix' => 'baz/prefix',
                            'alias' => 'bar/alias',
                            'uri' => 'bar/uri',
                            'link' => 'http://server.com/foo/custom',
                        )
                    ),
                    'routes' => array(
                        'bar_route' => array(
                            'server' => 'http://server.com/',
                            'uri' => 'bar/override',
                            'link' => 'http://server.com/bar/custom',
                        ),
                        'foo_route' => array(
                            'server' => 'http://server.com/',
                            'uri' => 'foo/uri',
                            'link' => 'http://server.com/foo',
                        )
                    ),
                )
            ),
        );
    }

    protected function createContainer(array $bundles = array())
    {
        $container = new ContainerBuilder(
            new ParameterBag(
                array(
                    'kernel.bundles'=> $bundles,
                )
            )
        );

        return $container;
    }
}
