<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Audit;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class AuditManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuditManager
     */
    private $auditManager;

    /**
     * @var ConfigManager
     */
    private $configManager;

    protected function setUp()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockForAbstractClass('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())->method('getUser')->will($this->returnValue($user));

        $securityContext = $this->getMockForAbstractClass('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->any())->method('getToken')->will($this->returnValue($token));

        $securityLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $securityLink->expects($this->any())->method('getService')->will($this->returnValue($securityContext));

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configManagerLink = $this->getMockBuilder(
            'Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $configManagerLink->expects($this->any())->method('getService')->will($this->returnValue($this->configManager));

        $provider = new ConfigProvider($this->configManager, new Container(), 'testScope', array());

        $this->configManager->expects($this->any())->method('getEntityManager')->will($this->returnValue($em));
        $this->configManager->expects($this->any())->method('getUpdateConfig')->will(
            $this->returnValue(
                array(
                    new Config(new EntityConfigId('testClass', 'testScope')),
                    new Config(new FieldConfigId('testClass', 'testScope', 'testField', 'string')),
                )
            )
        );

        $this->configManager->expects($this->any())->method('getProvider')->will($this->returnValue($provider));

        $this->auditManager = new AuditManager($configManagerLink, $securityLink);
    }

    protected function tearDown()
    {
        $this->auditManager = null;
    }

    public function testLog()
    {
        $this->configManager->expects($this->any())->method('getConfigChangeSet')->will(
            $this->returnValue(array('key' => 'value'))
        );

        $this->auditManager->log();
    }

    public function testLogWithOutChanges()
    {
        $this->configManager->expects($this->any())->method('getConfigChangeSet')->will(
            $this->returnValue(array())
        );

        $this->auditManager->log();
    }

    public function testLogWithoutUser()
    {
        $securityContext = $this->getMockForAbstractClass('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->any())->method('getToken');

        $securityLink = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $securityLink->expects($this->any())->method('getService')->will($this->returnValue($securityContext));

        $configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configManagerLink = $this->getMockBuilder(
            'Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $configManagerLink->expects($this->any())->method('getService')->will($this->returnValue($configManager));

        $auditManager = new AuditManager($configManagerLink, $securityLink);

        $auditManager->log();
    }
}
