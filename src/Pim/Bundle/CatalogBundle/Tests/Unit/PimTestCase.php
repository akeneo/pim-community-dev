<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit;

use Symfony\Component\Security\Core\SecurityContext;

/**
 * Abstract class for PIM unit tests
 * It purposes methods to build a set of mocks of differents classes
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class PimTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Create mock for locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $objectManager   = $this->getMockForAbstractClass('\Doctrine\Common\Persistence\ObjectManager');
        $securityContext = $this->getSecurityContextMock();

        // create mock builder for locale manager and redefine constructor to set object manager
        $mockBuilder = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
                            ->setConstructorArgs(array($objectManager, $securityContext));

        // create locale manager mock from mock builder previously create and redefine getActiveCodes method
        $localeManager = $mockBuilder->getMock(
            'Pim\Bundle\CatalogBundle\Manager\LocaleManager',
            array('getActiveCodes')
        );
        $localeManager->expects($this->any())
                      ->method('getActiveCodes')
                      ->will($this->returnValue(array('en_US', 'fr_FR')));

        return $localeManager;
    }

    /**
     * Create a security context mock
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContextMock()
    {
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $decisionManager = $this->getMock(
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface'
        );

        $securityContext = new SecurityContext($authManager, $decisionManager);
        $securityContext->setToken(
            $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
        );

        return $securityContext;
    }

    /**
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * Get a mock of EventDispatcherInterface
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    protected function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
