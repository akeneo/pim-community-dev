<?php

namespace Oro\Bundle\HelpBundle\Unit\Twig;

use Oro\Bundle\HelpBundle\Annotation\Help;
use Oro\Bundle\HelpBundle\Twig\HelpLinkProvider;

class HelpLinkProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configurationDataProvider
     * @param array $configuration
     * @param Help $annotation
     * @param string $link
     */
    public function testGetHelpLinkUrl($configuration, $annotation, $link)
    {
        $controller = 'Acme\\Bundle\\DemoBundle\\Controller\\TestController::runAction';
        $shortName = 'AcmeDemoBundle:Test:run';

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->exactly(2))
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request));

        $request->expects($this->at(0))
            ->method('get')
            ->with('_controller')
            ->will($this->returnValue($controller));
        $request->expects($this->at(1))
            ->method('get')
            ->with('oro_help')
            ->will($this->returnValue($annotation));

        $parser = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->expects($this->once())
            ->method('build')
            ->with($controller)
            ->will($this->returnValue($shortName));

        $provider = new HelpLinkProvider($parser, $container);
        $provider->setConfiguration($configuration);
        $this->assertEquals($link, $provider->getHelpLinkUrl());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function configurationDataProvider()
    {
        return array(
            'simple default' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/'
                    )
                ),
                null,
                'http://test.com/wiki/Acme/AcmeDemoBundle:Test_run'
            ),
            'default with prefix' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    )
                ),
                null,
                'http://test.com/wiki/Third_Party/Acme/AcmeDemoBundle:Test_run'
            ),
            'default with link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party',
                        'link' => 'http://wiki.test.com/'
                    )
                ),
                null,
                'http://wiki.test.com/'
            ),
            'vendor link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'vendors' => array(
                        'Acme' => array(
                            'link' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/'
            ),
            'vendor config' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'vendors' => array(
                        'Acme' => array(
                            'alias' => 'CustomVendor',
                            'prefix' => 'Prefix',
                            'server' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/Prefix/CustomVendor/AcmeDemoBundle:Test_run'
            ),
            'vendor uri' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'vendors' => array(
                        'Acme' => array(
                            'uri' => 'test'
                        )
                    )
                ),
                null,
                'http://test.com/wiki/test'
            ),
            'bundle config' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle' => array(
                            'alias' => 'CustomBundle',
                            'prefix' => 'Prefix',
                            'server' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/Prefix/Acme/CustomBundle:Test_run'
            ),
            'bundle link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle' => array(
                            'link' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/'
            ),
            'bundle uri' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle' => array(
                            'uri' => 'test'
                        )
                    )
                ),
                null,
                'http://test.com/wiki/test'
            ),
            'controller config' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test' => array(
                            'alias' => 'MyTest',
                            'prefix' => 'Prefix',
                            'server' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/Prefix/Acme/AcmeDemoBundle:MyTest_run'
            ),
            'controller link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test' => array(
                            'link' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/'
            ),
            'controller uri' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test' => array(
                            'uri' => 'test'
                        )
                    )
                ),
                null,
                'http://test.com/wiki/test'
            ),
            'action config' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test:run' => array(
                            'alias' => 'execute',
                            'prefix' => 'Prefix',
                            'server' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/Prefix/Acme/AcmeDemoBundle:Test_execute'
            ),
            'action link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test:run' => array(
                            'link' => 'http://wiki.test.com/'
                        )
                    )
                ),
                null,
                'http://wiki.test.com/'
            ),
            'action uri' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'resources' => array(
                        'AcmeDemoBundle:Test:run' => array(
                            'uri' => 'test'
                        )
                    )
                ),
                null,
                'http://test.com/wiki/test'
            ),
            'annotation link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    )
                ),
                new Help(array('link' => 'http://wiki.test.com/')),
                'http://wiki.test.com/'
            ),
            'annotation configuration' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    )
                ),
                new Help(
                    array(
                        'actionAlias' => 'execute',
                        'controllerAlias' => 'Executor',
                        'bundleAlias' => 'Bundle',
                        'vendorAlias' => 'Vendor',
                        'prefix' => 'Prefix',
                        'server' => 'http://wiki.test.com/'
                    )
                ),
                'http://wiki.test.com/Prefix/Vendor/Bundle:Executor_execute'
            ),
            'annotation uri' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    )
                ),
                new Help(
                    array(
                        'uri' => 'test',
                        'server' => 'http://wiki.test.com/'
                    )
                ),
                'http://wiki.test.com/test'
            ),
        );
    }
}
