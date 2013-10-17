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
        $this->manager    = new MediaManager($this->filesystem, '/tmp/upload');
    }

    protected function tearDown()
    {
        $this->filesystem = null;
        $this->manager    = null;
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

    /**
     * @param string $name
     * @param mixed  $filesystem
     *
     * @return \Knp\Bundle\GaufretteBundle\FilesystemMap
     */
    private function getFilesystemMapMock($name, $filesystem)
    {
        $filesystemMap = $this
            ->getMockBuilder('Knp\Bundle\GaufretteBundle\FilesystemMap')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $filesystemMap->expects($this->any())
                      ->method('get')
                      ->with($this->equalTo($name))
                      ->will($this->returnValue($filesystem));

        return $filesystemMap;
    }

    /**
     * @param mixed $file
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    private function getMediaMock($file = null)
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
    private function getFileMock()
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
    private function getFilesystemMock()
    {
        $filesystem = $this
            ->getMockBuilder('Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        return $filesystem;
    }
}
