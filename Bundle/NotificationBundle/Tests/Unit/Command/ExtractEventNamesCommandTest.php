<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Command;

use Oro\Bundle\NotificationBundle\Command\ExtractEventNamesCommand;

class ExtractEventNamesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtractEventNamesCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $application;

    public function setUp()
    {
        $this->command = new ExtractEventNamesCommand();
        $this->application = $this->getMock('Symfony\Component\Console\Application');

        $helper = $this->getMock('Symfony\Component\Console\Helper\HelperSet');
        $this->application->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnValue($helper));

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->command->setContainer($this->container);
        $this->command->setApplication($this->application);
    }

    public function tearDown()
    {
        unset($this->command);
        unset($this->application);
        unset($this->container);
    }

    public function testConfiguration()
    {
        $this->assertNotEmpty($this->command->getDescription());
        $this->assertNotEmpty($this->command->getName());

        $this->assertTrue($this->command->getDefinition()->hasArgument('bundle'));
        $this->assertTrue($this->command->getDefinition()->hasOption('oro-only'));
    }

    public function testExecute()
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())
            ->method('getArgument')
            ->with($this->equalTo('bundle'))
            ->will($this->returnValue(null));
        $input->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('oro-only'))
            ->will($this->returnValue(false));

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $extractor = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor')
            ->disableOriginalConstructor()
            ->getMock();
        $extractor->expects($this->once())
            ->method('extract')
            ->with($this->equalTo('Oro\Bundles\OroAbcBundle'));
        $extractor->expects($this->once())
            ->method('dumpToDb');

        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('OroAbcBundle'));
        $bundle->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('Oro\Bundles\OroAbcBundle'));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($bundle)));

        $this->container->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('oro_notification.event_names.extractor'))
            ->will($this->returnValue($extractor));

        $this->container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('kernel'))
            ->will($this->returnValue($kernel));

        $command = $this->getMockBuilder('Symfony\Component\Console\Command\Command')
            ->disableOriginalConstructor()
            ->getMock();
        $command->expects($this->once())
            ->method('run');

        $this->application->expects($this->once())
            ->method('find')
            ->with($this->equalTo('cache:clear'))
            ->will($this->returnValue($command));

        $this->command->execute($input, $output);
    }

    /**
     * @dataProvider bundleDirsProvider
     * @param null $bundleName
     * @param bool $oroOnly
     */
    public function testGetBundleDirs($bundleName, $oroOnly)
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(is_null($bundleName) ? 'TratataAbcBundle' : $bundleName));

        if (!$oroOnly) {
            $bundle->expects($this->once())
                ->method('getPath')
                ->will($this->returnValue('Oro\Bundles\OroAbcBundle'));
        }

        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($bundle)));

        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('kernel'))
            ->will($this->returnValue($kernel));

        $dirs = $this->command->getBundleDirs($bundleName, $oroOnly);

        if ($oroOnly) {
            $this->assertEmpty($dirs);
        } elseif (is_null($bundleName)) {
            $this->assertEquals($dirs, array('TratataAbcBundle' => 'Oro\Bundles\OroAbcBundle'));
        }
    }

    /**
     * data provider
     */
    public function bundleDirsProvider()
    {
        return array(
            'all bundles'                  => array(null, false),
            'oro only'                     => array(null, true),
            'oro bundle'                   => array('OroAbcBundle', false),
            'not oro bundle with oro only' => array('AbcAbcBundle', true)
        );
    }
}
