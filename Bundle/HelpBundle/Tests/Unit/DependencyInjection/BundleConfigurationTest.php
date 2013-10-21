<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\HelpBundle\DependencyInjection\BundleConfiguration;
use Symfony\Component\Config\Definition\Processor;

class BundleConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processConfigurationDataProvider
     */
    public function testProcessConfiguration($options, $expects)
    {
        $processor = new Processor();
        $configuration = new BundleConfiguration();
        $result = $processor->processConfiguration($configuration, array($options));

        $this->assertEquals($expects, $result);
    }

    public function processConfigurationDataProvider()
    {
        return array(
            'minimal_config' => array(
                array(),
                array(
                    'vendors' => array(),
                    'resources' => array(),
                    'routes' => array(),
                )
            ),
            'extend_config' => array(
                array(
                    'vendors' => array(
                        'Oro' => array(
                            'alias' => 'Platform'
                        ),
                    ),
                    'resources' => array(
                        'AcmeFooBundle' => array(
                            'server' => 'http://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        ),
                        'AcmeFooBundle:Foo' => array(
                            'server' => 'https://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        ),
                        'AcmeFooBundle:Foo:bar' => array(
                            'server' => 'http://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        ),
                    ),
                    'routes' => array(
                        'test_route' => array(
                            'server' => 'http://server.com',
                            'uri' => 'uri',
                            'link' => 'link',
                        ),
                    ),
                ),
                array(
                    'vendors' => array(
                        'Oro' => array(
                            'alias' => 'Platform'
                        ),
                    ),
                    'resources' => array(
                        'AcmeFooBundle' => array(
                            'server' => 'http://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        ),
                        'AcmeFooBundle:Foo' => array(
                            'server' => 'https://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        ),
                        'AcmeFooBundle:Foo:bar' => array(
                            'server' => 'http://server.com',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server.com/link',
                        )
                    ),
                    'routes' => array(
                        'test_route' => array(
                            'server' => 'http://server.com',
                            'uri' => 'uri',
                            'link' => 'link',
                        ),
                    ),
                )
            ),
        );
    }
    /**
     * @dataProvider processConfigurationFailsDataProvider
     */
    public function testProcessConfigurationFails($options, $expectedException, $expectedExceptionMessage)
    {
        $processor = new Processor();
        $configuration = new BundleConfiguration();

        $this->setExpectedException($expectedException, $expectedExceptionMessage);

        $processor->processConfiguration($configuration, array($options));
    }

    public function processConfigurationFailsDataProvider()
    {
        return array(
            'invalid_resource' => array(
                array(
                    'resources' => array(
                        '123' => array()
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Node "resources" contains invalid resource name "123".'
            ),
            'invalid_server' => array(
                array(
                    'resources' => array(
                        'AcmeFooBundle:Foo:bar' => array(
                            'server' => 'server',
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Invalid configuration for path "resources.resources.AcmeFooBundle:Foo:bar.server": '
                    . 'Invalid URL "server".'
            ),
            'invalid_link' => array(
                array(
                    'resources' => array(
                        'AcmeFooBundle:Foo:bar' => array(
                            'link' => 'link',
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Invalid configuration for path "resources.resources.AcmeFooBundle:Foo:bar.link": Invalid URL "link".'
            ),
            'invalid_vendor_too_many_sections' => array(
                array(
                    'resources' => array(
                        'AcmeFooBundle:Foo:bar:baz' => array()
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Node "resources" contains invalid resource name "AcmeFooBundle:Foo:bar:baz".'
            ),
        );
    }
}
