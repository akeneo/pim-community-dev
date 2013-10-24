<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\HelpBundle\DependencyInjection\ApplicationConfiguration;
use Symfony\Component\Config\Definition\Processor;

class ApplicationConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processConfigurationDataProvider
     */
    public function testProcessConfiguration($options, $expects)
    {
        $processor = new Processor();
        $configuration = new ApplicationConfiguration();
        $result = $processor->processConfiguration($configuration, array($options));

        $this->assertEquals($expects, $result);
    }

    public function processConfigurationDataProvider()
    {
        return array(
            'minimal_config' => array(
                array(
                    'defaults' => array('server' => 'http://server')
                ),
                array(
                    'defaults' => array('server' => 'http://server'),
                    'vendors' => array(),
                    'resources' => array(),
                    'routes' => array(),
                )
            ),
            'extended_config' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://server',
                        'prefix' => 'prefix',
                        'uri' => 'uri',
                        'link' => 'http://server/link'
                    ),
                    'vendors' => array('Oro' => array('alias' => 'Platform')),
                    'resources' => array(
                        'AcmeFooBundle' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeFooBundle:Foo' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeFooBundle:Foo:bar' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeBarBundle' => array(),
                    ),
                    'routes' => array(
                        'test_route' => array(
                            'server' => 'http://server.com',
                            'uri' => 'uri',
                            'link' => 'link',
                        ),
                    )
                ),
                array(
                    'defaults' => array(
                        'server' => 'http://server',
                        'prefix' => 'prefix', 'uri' => 'uri', 'link' => 'http://server/link'
                    ),
                    'vendors' => array(
                        'Oro' => array('alias' => 'Platform')
                    ),
                    'resources' => array(
                        'AcmeFooBundle' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeFooBundle:Foo' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeFooBundle:Foo:bar' => array(
                            'server' => 'http://server',
                            'prefix' => 'prefix',
                            'alias' => 'alias',
                            'uri' => 'uri',
                            'link' => 'http://server/link',
                        ),
                        'AcmeBarBundle' => array()
                    ),
                    'routes' => array(
                        'test_route' => array('server' => 'http://server.com', 'uri' => 'uri', 'link' => 'link')
                    )
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
        $configuration = new ApplicationConfiguration();

        $this->setExpectedException($expectedException, $expectedExceptionMessage);

        $processor->processConfiguration($configuration, array($options));
    }

    public function processConfigurationFailsDataProvider()
    {
        return array(
            'no_defaults' => array(
                array(),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The child node "defaults" at path "oro_help" must be configured.'
            ),
            'no_server' => array(
                array(
                    'defaults' => array()
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The child node "server" at path "oro_help.defaults" must be configured.'
            ),
            'invalid_server' => array(
                array(
                    'defaults' => array(
                        'server' => 'server'
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Invalid configuration for path "oro_help.defaults.server": Invalid URL "server".'
            ),
            'invalid_link' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://server',
                        'link' => 'link'
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Invalid configuration for path "oro_help.defaults.link": Invalid URL "link".'
            ),
            'invalid_vendor_name' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://server'
                    ),
                    'vendors' => array(
                        '123' => array()
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'Node "vendors" contains invalid vendor name "123".'
            ),
        );
    }
}
