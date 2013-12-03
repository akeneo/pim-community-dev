<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Security;

use Oro\Bundle\SearchBundle\Security\SecurityProvider;

class SecurityProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityProvider
     */
    protected $provider;

    protected $securityFacade;
    protected $entitySecurityMetadataProvider;

    public function setUp()
    {
        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entitySecurityMetadataProvider
            = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Metadata\EntitySecurityMetadataProvider')
                ->disableOriginalConstructor()
                ->getMock();
        $this->provider = new SecurityProvider($this->securityFacade, $this->entitySecurityMetadataProvider);
    }

    public function testIisProtectedEntity()
    {
        $this->entitySecurityMetadataProvider->expects($this->once())
            ->method('isProtectedEntity')
            ->with('someClass')
            ->will($this->returnValue(true));
        $this->provider->isProtectedEntity('someClass');
    }

    public function testIsGranted()
    {
        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->with('VIEW', 'someClass')
            ->will($this->returnValue(true));
        $this->provider->isGranted('VIEW', 'someClass');
    }
}
