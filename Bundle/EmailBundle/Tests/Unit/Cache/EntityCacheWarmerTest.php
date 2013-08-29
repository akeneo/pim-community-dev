<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Cache;

use Oro\Bundle\EmailBundle\Cache\EntityCacheWarmer;

class EntityCacheWarmerTest extends \PHPUnit_Framework_TestCase
{
    public function testWarmUpAndIsOptional()
    {
        $storage = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $oroProvider = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $oroProvider->expects($this->once())
            ->method('getEmailOwnerClass')
            ->will($this->returnValue('Oro\TestUser'));

        $oroCrmProvider = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $oroCrmProvider->expects($this->once())
            ->method('getEmailOwnerClass')
            ->will($this->returnValue('OroCRM\TestContact'));

        $acmeProvider = $this->getMock('Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface');
        $acmeProvider->expects($this->once())
            ->method('getEmailOwnerClass')
            ->will($this->returnValue('Acme\TestUser'));

        $storage->expects($this->once())
            ->method('getProviders')
            ->will($this->returnValue(array($oroProvider, $oroCrmProvider, $acmeProvider)));

        $warmer = $this->getMockBuilder('Oro\Bundle\EmailBundle\Cache\EntityCacheWarmer')
            ->setConstructorArgs(array($storage, 'SomeDir', 'Test\SomeNamespace', 'Test%sProxy'))
            ->setMethods(array('createFilesystem', 'createTwigEnvironment', 'writeCacheFile'))
            ->getMock();

        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $warmer->expects($this->once())
            ->method('createFilesystem')
            ->will($this->returnValue($fs));

        $warmer->expects($this->once())
            ->method('createTwigEnvironment')
            ->will($this->returnValue($twig));

        $fs->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('SomeDir/Test/SomeNamespace'))
            ->will($this->returnValue(false));

        $fs->expects($this->once())
            ->method('mkdir')
            ->with($this->equalTo('SomeDir/Test/SomeNamespace'), $this->equalTo(0777));

        $twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('EmailAddress.php.twig'),
                $this->equalTo(
                    array(
                        'namespace' => 'Test\SomeNamespace',
                        'className' => 'TestEmailAddressProxy',
                        'owners' => array(
                            array(
                                'targetEntity' => 'Oro\TestUser',
                                'columnName' => 'owner_testuser_id',
                                'fieldName' => 'owner1'
                            ),
                            array(
                                'targetEntity' => 'OroCRM\TestContact',
                                'columnName' => 'owner_testcontact_id',
                                'fieldName' => 'owner2'
                            ),
                            array(
                                'targetEntity' => 'Acme\TestUser',
                                'columnName' => 'owner_acme_testuser_id',
                                'fieldName' => 'owner3'
                            ),
                        )
                    )
                )
            )
            ->will($this->returnValue('testContent'));

        $warmer->expects($this->once())
            ->method('writeCacheFile')
            ->with(
                $this->equalTo('SomeDir/Test/SomeNamespace/TestEmailAddressProxy.php'),
                $this->equalTo('testContent')
            );

        $warmer->warmup('');
        $this->assertFalse($warmer->isOptional());
    }
}
