<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Manager;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Gaufrette\Filesystem */
    protected $filesystem;

    /** @var \Pim\Bundle\CatalogBundle\Manager\MediaManager */
    protected $manager;

    protected function setUp()
    {
        $this->filesystem = $this->getFilesystemMock();
        $this->uploadDir  = '/tmp/upload';
        $this->manager    = new MediaManager($this->filesystem, $this->uploadDir);
    }

    protected function tearDown()
    {
        $this->filesystem = null;
        $this->manager    = null;
        @unlink($this->uploadDir . '/phpunit-file.txt');
    }

    public function testUploadIfAFileIsPresent()
    {
        $this->filesystem->expects($this->once())
                   ->method('write')
                   ->with(
                       $this->equalTo('foo-akeneo.jpg'),
                       $this->anything(),
                       $this->equalTo(false)
                   );

        $this->filesystem->expects($this->at(0))
                   ->method('has')
                   ->will($this->returnValue(false));

        $this->filesystem->expects($this->at(2))
                   ->method('has')
                   ->will($this->returnValue(true));

        $media = $this->getMediaMock($this->getFileMock());

        $media->expects($this->any())
              ->method('getFilename')
              ->will($this->returnValue('foo-akeneo.jpg'));

        $media->expects($this->once())
              ->method('setOriginalFilename')
              ->with('akeneo.jpg');

        $media->expects($this->once())
              ->method('setFilename')
              ->with($this->equalTo('foo-akeneo.jpg'));

        $media->expects($this->once())
              ->method('setFilepath')
              ->with($this->equalTo('/tmp/upload/foo-akeneo.jpg'));

        $media->expects($this->once())
              ->method('setMimeType')
              ->with($this->equalTo('image/jpeg'));

        $this->manager->handle($media, 'foo');
    }

    public function testRemoveAFileIfMediaIsRemoved()
    {
        $this->filesystem->expects($this->any())
                   ->method('has')
                   ->will($this->returnValue(true));

        $this->filesystem->expects($this->once())
                   ->method('delete');

        $media = $this->getMediaMock();

        $media->expects($this->any())
              ->method('isRemoved')
              ->will($this->returnValue(true));

        $media->expects($this->any())
            ->method('getFilename')
            ->will($this->returnValue('foo.jpg'));

        $this->manager->handle($media, '');
    }

    public function testExportMedia()
    {
        @mkdir($this->uploadDir, 0777, true);
        file_put_contents($this->uploadDir . '/phpunit-file.txt', 'Lorem ipsum');

        $media = $this->getMediaMock();
        $media->expects($this->any())
            ->method('getFilePath')
            ->will($this->returnValue($this->uploadDir . '/phpunit-file.txt'));
        $media->expects($this->any())
            ->method('getFilename')
            ->will($this->returnValue('phpunit-file.txt'));

        $this->assertEquals(
            '/tmp/behat/phpunit-file.txt',
            $this->manager->copy($media, '/tmp/behat/')
        );

        $this->assertFileEquals(
            $this->uploadDir . '/phpunit-file.txt',
            '/tmp/behat/phpunit-file.txt'
        );
    }

    /**
     * @param mixed $file
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    protected function getMediaMock($file = null)
    {
        $media = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Media');

        $media->expects($this->any())
              ->method('getFile')
              ->will($this->returnValue($file));

        return $media;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected function getFileMock()
    {
        $file = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();

        $bundle = new \ReflectionClass('Pim\Bundle\CatalogBundle\PimCatalogBundle');

        $file->expects($this->any())
             ->method('getPathname')
             ->will($this->returnValue(sprintf('%s/Tests/fixtures/akeneo.jpg', dirname($bundle->getFileName()))));

        $file->expects($this->any())
             ->method('getClientOriginalName')
             ->will($this->returnValue('akeneo.jpg'));

        $file->expects($this->any())
             ->method('getMimeType')
             ->will($this->returnValue('image/jpeg'));

        return $file;
    }

    /**
     * @return \Knp\Bundle\GaufretteBundle\Filesystem
     */
    protected function getFilesystemMock()
    {
        $filesystem = $this
            ->getMockBuilder('Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        return $filesystem;
    }
}
