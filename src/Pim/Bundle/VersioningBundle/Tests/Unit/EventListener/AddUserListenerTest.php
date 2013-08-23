<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\EventListener;

use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpKernel\KernelEvents;
use Pim\Bundle\VersioningBundle\Manager\VersionBuilder;
use Pim\Bundle\VersioningBundle\EventListener\AddUserListener;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUserListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\VersioningBundle\EventListener\AddVersionListener
     */
    protected $versionListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $encoders = array(new CsvEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $builder = new VersionBuilder($serializer);
        $this->versionListener = new AddVersionListener($builder);
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(AddUserListener::getSubscribedEvents(), array(KernelEvents::REQUEST => 'onKernelRequest'));
    }

    /**
     * Test related method
     */
    public function testOnKernelRequestWithoutContext()
    {
        $userListener = new AddUserListener($this->versionListener);
        $getResponseEventMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertNull($userListener->onKernelRequest($getResponseEventMock));
    }

    /**
     * Test related method
     */
    public function testOnKernelRequestWithContext()
    {
        $securityContextMock = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContextMock->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue('1234'));
        $securityContextMock->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $userListener = new AddUserListener($this->versionListener, $securityContextMock);

        $getResponseEventMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertNull($userListener->onKernelRequest($getResponseEventMock));
    }
}
