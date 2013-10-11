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

        $provider = new HelpLinkProvider($parser, $request);
        $provider->setConfiguration($configuration);
        $this->assertEquals($link, $provider->getHelpLinkUrl());
    }

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
            'vendor' => array(
                array(
                    'defaults' => array(
                        'server' => 'http://test.com/wiki/',
                        'prefix' => 'Third_Party'
                    ),
                    'vendors' => array(
                        'Acme' => array(
                            'server' => 'http://custom.org/wiki/',
                            'prefix' => null,
                            'alias' => 'CustomVendor'
                        )
                    )
                ),
                null,
                'http://custom.org/wiki/CustomVendor/AcmeDemoBundle:Test_run'
            ),
        );
    }
}
