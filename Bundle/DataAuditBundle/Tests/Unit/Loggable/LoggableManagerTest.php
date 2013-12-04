<?php
namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Loggable;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;
use Oro\Bundle\DataAuditBundle\Metadata\ClassMetadata;

use Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass;
use Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableCollectionClass;
use Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata\AbstractMetadataTest;

use Oro\Bundle\UserBundle\Entity\User;

class LoggableManagerTest extends AbstractMetadataTest
{
    /**
     * @var LoggableManager
     */
    protected $loggableManager;

    /**
     * @var ClassMetadata
     */
    protected $config;

    /**
     * @var LoggableClass
     */
    protected $loggableClass;

    public function setUp()
    {
        parent::setUp();

        $meta = $this->em->getClassMetadata('Oro\Bundle\UserBundle\Entity\User');
        $meta->setCustomRepositoryClass('Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\Repository\UserRepository');

        $this->config = $this->loggableAnnotationDriver->extendLoadMetadataForClass(
            $this->em->getClassMetadata('Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass')
        );

        $provider = $this->getMockBuilder('\Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $provider
            ->expects($this->any())
            ->method('isConfigurable')
            ->will($this->returnValue(false));

        $this->loggableManager = new LoggableManager('Oro\Bundle\DataAuditBundle\Entity\Audit', $provider);
        $this->loggableManager->addConfig($this->config);

        $this->loggableClass = new LoggableClass();
        $this->loggableClass->setName('testName');
    }

    public function testHandleLoggable()
    {
        $loggableCollectionClass = new LoggableCollectionClass();
        $loggableCollectionClass->setName('testCollectionName');

        $collection = new PersistentCollection(
            $this->em,
            get_class($loggableCollectionClass),
            array($loggableCollectionClass)
        );
        $collection->setDirty(true);
        $this->loggableClass->setCollection($collection);

        $this->em->persist($this->loggableClass);

        //log with out user
        $this->loggableManager->handleLoggable($this->em);

        //log with user
        $this->loggableManager->setUsername('testUser');
        $this->loggableManager->handleLoggable($this->em);

        //log delete
        $this->em->remove($this->loggableClass);
        $this->loggableManager->handleLoggable($this->em);
    }

    public function testHandlePostPersist()
    {
        $this->loggableManager->handlePostPersist($this->loggableClass, $this->em);
    }

    public function testSetUsername()
    {
        $user = new User();
        $user->setUsername('testuser');

        $this->loggableManager->setUsername($user);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Username must be a string, or object should have method: getUsername'
        );
        $wrongUser = new \stdClass();
        $this->loggableManager->setUsername($wrongUser);
    }

    public function testGetConfig()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClassWrong'
        );
        $this->loggableManager->getConfig('Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClassWrong');

        $resultConfig = $this->loggableManager->getConfig(
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\LoggableClass'
        );

        $this->assertEquals($this->config, $resultConfig);
    }
}
