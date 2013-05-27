<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity\EventListener;

use Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface;
use Oro\Bundle\UserBundle\Entity\EventListener\UploadedImageSubscriber;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class UploadedImageSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $tmpRootPath;

    protected function setUp()
    {
        $this->tmpRootPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'oro_unit';
        $this->removeTmpDir();
        mkdir($this->tmpRootPath . DIRECTORY_SEPARATOR . 'app', 0755, true);
        mkdir($this->getUploadRootDir(), 0755, true);
    }

    protected function tearDown()
    {
        $this->removeTmpDir();
    }

    public function testSetKernelRoot()
    {
        $subscriber = $this->getSubscriber();
        $this->assertAttributeEquals($this->tmpRootPath . DIRECTORY_SEPARATOR . 'web', 'webRoot', $subscriber);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid kernel root
     */
    public function testInvalidKernelRoot()
    {
        new UploadedImageSubscriber('/a' . mt_rand() . 'sbsdf' . mt_rand());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', $this->getSubscriber()->getSubscribedEvents());
    }

    public function testPostRemove()
    {
        $fileName = md5(time()) . '.jpg';
        $uploadDir = 'uploads/post-remove-test';

        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $this->assertImageRemoveConditions($entity, $fileName, $uploadDir);

        $this->getSubscriber()->postRemove($this->getEvent($entity));
        $this->assertFileNotExists($this->getImagePath($entity));
    }

    protected function assertImageRemoveConditions($entity, $fileName, $uploadDir)
    {
        $entity->expects($this->atLeastOnce())
            ->method('getImage')
            ->will($this->returnValue($fileName));
        $entity->expects($this->atLeastOnce())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));

        $this->prepareImage($entity);
        $this->assertFileExists($this->getImagePath($entity));
    }

    public function testPrePersist()
    {
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $this->assertImageUpdate($entity);

        $this->getSubscriber()->prePersist($this->getEvent($entity));
    }

    protected function assertImageUpdate($entity)
    {
        $imageFile = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $imageFile->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('jpg'));

        $entity->expects($this->atLeastOnce())
            ->method('getImageFile')
            ->will($this->returnValue($imageFile));
        $entity->expects($this->once())
            ->method('setImage')
            ->with($this->stringEndsWith('.jpg'))
            ->will($this->returnSelf());
    }

    public function testPreUpdate()
    {
        $fileName = md5(time()) . '.jpg';
        $uploadDir = 'uploads/pre-update-test';

        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $this->assertImageRemoveConditions($entity, $fileName, $uploadDir);
        $this->assertImageUpdate($entity);

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $uow->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);

        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->will($this->returnValue($metadata));

        $this->getSubscriber()->preUpdate($this->getEvent($entity, $em));
        $this->assertFileNotExists($this->getImagePath($entity));
    }

    public function testHandleImageUploadNoImage()
    {
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $entity->expects($this->never())
            ->method('getUploadDir');
        $entity->expects($this->exactly(2))
            ->method('getImageFile');

        $event = $this->getEvent($entity);
        $subscriber = $this->getSubscriber();
        $subscriber->postPersist($event);
        $subscriber->postUpdate($event);
    }

    public function testPostUpdate()
    {
        $uploadDir = 'uploads/image-upload-test';
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $this->assertImageUploadPreconditions($entity, $uploadDir);
        $this->getSubscriber()->postUpdate($this->getEvent($entity));
        $this->assertFileExists($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $uploadDir);
    }

    public function testPostPersist()
    {
        $uploadDir = 'uploads/image-upload-test';
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface');
        $this->assertImageUploadPreconditions($entity, $uploadDir);
        $this->getSubscriber()->postPersist($this->getEvent($entity));
        $this->assertFileExists($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $uploadDir);
    }

    public function assertImageUploadPreconditions($entity, $uploadDir)
    {
        $fileName = md5(time()) . '.jpg';


        $imageFile = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $dir = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $uploadDir;
        $imageFile->expects($this->once())
            ->method('move')
            ->with($dir, $fileName);

        $entity->expects($this->once())
            ->method('getUploadDir')
            ->will($this->returnValue($uploadDir));
        $entity->expects($this->exactly(2))
            ->method('getImageFile')
            ->will($this->returnValue($imageFile));
        $entity->expects($this->once())
            ->method('getImage')
            ->will($this->returnValue($fileName));
        $entity->expects($this->once())
            ->method('unsetImageFile');
    }

    protected function removeTmpDir()
    {
        if (!is_dir($this->tmpRootPath)) {
            return;
        }
        $it = new RecursiveDirectoryIterator($this->tmpRootPath);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        /** @var \SplFileObject $file */
        foreach ($files as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->tmpRootPath);
    }

    protected function getImagePath(EntityUploadedImageInterface $entity)
    {
        $uploadDir = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $entity->getUploadDir();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        return $uploadDir . DIRECTORY_SEPARATOR . $entity->getImage();
    }

    protected function prepareImage(EntityUploadedImageInterface $entity)
    {
        $path = $this->getImagePath($entity);
        copy($this->getFixtureImagePath(), $path);
    }

    protected function getUploadRootDir()
    {
        return $this->tmpRootPath . DIRECTORY_SEPARATOR . 'web';
    }

    protected function getFixtureImagePath()
    {
        return realpath(__DIR__ . '/../../Fixture/files/test_image.jpg');
    }

    protected function getEvent($entity, $em = null)
    {
        $event = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));
        if ($em) {
            $event->expects($this->any())
                ->method('getEntityManager')
                ->will($this->returnValue($em));
        }
        return $event;
    }

    protected function getSubscriber()
    {
        return new UploadedImageSubscriber($this->tmpRootPath . DIRECTORY_SEPARATOR . 'app');
    }
}
