<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $configManager;

    public function setUp()
    {
        $metadataFactory = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $providerBag = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderBag')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLink->expects($this->any())->method('getService')->will($this->returnValue($providerBag));

        $configModelManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $auditManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\AuditManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager = new ConfigManager(
            $metadataFactory,
            $eventDispatcher,
            $serviceLink,
            $configModelManager,
            $auditManager
        );
    }
}
