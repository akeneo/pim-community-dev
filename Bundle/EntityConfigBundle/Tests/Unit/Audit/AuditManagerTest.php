<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Audit;

use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class AuditManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuditManager
     */
    private $auditManager;

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

        $securityProxy = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy')
            ->disableOriginalConstructor()
            ->getMock();
        $securityProxy->expects($this->any())->method('getService')->will($this->returnValue($securityContext));

        $configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new ConfigProvider($configManager, new EntityConfigContainer('testScope', array()));

        $configManager->expects($this->any())->method('em')->will($this->returnValue($em));

        $configManager->expects($this->any())->method('getUpdatedEntityConfig')->will(
            $this->returnValue(
                array(
                    new EntityConfig('testClass', 'testScope'),
                    new FieldConfig('testClass', 'testField', 'string', 'testScope'),
                )
            )
        );
        $configManager->expects($this->any())->method('getUpdatedFieldConfig')->will($this->returnValue(array()));
        $configManager->expects($this->any())->method('getConfigChangeSet')->will($this->returnValue(array('key' => 'value')));
        $configManager->expects($this->any())->method('getProvider')->will($this->returnValue($provider));

        $this->auditManager = new AuditManager($configManager, $securityProxy);
    }

    protected function tearDown()
    {
        $this->auditManager = null;
    }

    public function testLog()
    {
        $this->auditManager->log();
    }

    public function testLogWithoutUser()
    {
        $securityContext = $this->getMockForAbstractClass('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->any())->method('getToken');

        $securityProxy = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy')
            ->disableOriginalConstructor()
            ->getMock();
        $securityProxy->expects($this->any())->method('getService')->will($this->returnValue($securityContext));

        $configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $auditManager = new AuditManager($configManager, $securityProxy);

        $auditManager->log();
    }
}
