<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector;

use Oro\Bundle\ImapBundle\Connector\ImapServicesFactory;
use Oro\Bundle\ImapBundle\Connector\ImapConfig;
use Oro\Bundle\ImapBundle\Connector\ImapServices;

class ImapServicesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Oro\Bundle\ImapBundle\Connector\Exception\InvalidConfigurationException
     */
    public function testMissingDefaultServices()
    {
        new ImapServicesFactory(array('TEST' => array('StorageClass', 'SearchStringManagerClass')));
    }

    public function testCreateImapServicesForDefaultServices()
    {
        $config = array(
            '' => array(
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\Imap1',
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\SearchStringManager1'
            ),
            'FEATURE2' => array(
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\Imap2',
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\SearchStringManager2'
            )
        );

        $factory = new ImapServicesFactory($config);

        $services = $factory->createImapServices(new ImapConfig());

        $expected = new ImapServices(new TestFixtures\Imap1(array()), new TestFixtures\SearchStringManager1());

        $this->assertEquals($expected, $services);
    }

    public function testCreateImapServicesForOtherServices()
    {
        $config = array(
            '' => array(
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\Imap2',
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\SearchStringManager2'
            ),
            'FEATURE2' => array(
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\Imap1',
                'Oro\Bundle\ImapBundle\Tests\Unit\Connector\TestFixtures\SearchStringManager1'
            )
        );

        $factory = new ImapServicesFactory($config);

        $services = $factory->createImapServices(new ImapConfig());

        $expected = new ImapServices(new TestFixtures\Imap1(array()), new TestFixtures\SearchStringManager1());

        $this->assertEquals($expected, $services);
    }
}
