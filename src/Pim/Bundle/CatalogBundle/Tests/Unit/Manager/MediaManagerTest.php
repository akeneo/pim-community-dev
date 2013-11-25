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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->filesystem = $this->getFilesystemMock();
        $this->uploadDir  = '/tmp/upload';
        $this->manager    = new MediaManager($this->filesystem, $this->uploadDir);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->filesystem = null;
        $this->manager    = null;
        @unlink($this->uploadDir . '/phpunit-file.txt');
    }

    /**
     * Test related method
     */
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

    /**
     * Test related method
     */
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
     * Test related method
     */
    public function testCopyMedia()
    {
        @mkdir($this->uploadDir, 0777, true);
        file_put_contents($this->uploadDir . '/phpunit-file.txt', 'Lorem ipsum');

        $media     = $this->getMediaMock();
        $entity    = $this->getProductMock('sku000');
        $attribute = $this->getAttributeMock('mockFile');
        $value     = $this->getValueMock($entity, $attribute);

        $media->expects($this->any())
            ->method('getFilePath')
            ->will($this->returnValue($this->uploadDir . '/phpunit-file.txt'));

        $media->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($value));

        $media->expects($this->any())
            ->method('getOriginalFilename')
            ->will($this->returnValue('phpunit-file.txt'));

        $this->assertTrue(
            $this->manager->copy($media, '/tmp/behat/')
        );

        $this->assertFileEquals(
            $this->uploadDir . '/phpunit-file.txt',
            '/tmp/behat/files/sku000/mockFile/phpunit-file.txt'
        );
    }

    /**
     * @param string $locale
     * @param string $scope
     * @param string $exportPath
     *
     * @dataProvider getExportPathData
     */
    public function testGetExportPath($locale, $scope, $exportPath)
    {
        $media     = $this->getMediaMock();
        $entity    = $this->getProductMock('sku000');
        $attribute = $this->getAttributeMock('mockFile', $locale !== '', $scope !== '');
        $value     = $this->getValueMock($entity, $attribute, $locale, $scope);

        $media->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($value));

        $media->expects($this->any())
            ->method('getOriginalFilename')
            ->will($this->returnValue('phpunit-file.txt'));

        $media->expects($this->any())
            ->method('getFilePath')
            ->will($this->returnValue('filePath'));

        $this->assertEquals($exportPath, $this->manager->getExportPath($media));
    }

    /**
     * @return array
     */
    public static function getExportPathData()
    {
        return array(
            array('',      '',          'files/sku000/mockFile/phpunit-file.txt'),
            array('en_US', '',          'files/sku000/mockFile/en_US/phpunit-file.txt'),
            array('',      'ecommerce', 'files/sku000/mockFile/ecommerce/phpunit-file.txt'),
            array('en_US', 'ecommerce', 'files/sku000/mockFile/en_US/ecommerce/phpunit-file.txt'),
        );
    }

    /**
     * @param mixed $file
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Media
     */
    protected function getMediaMock($file = null)
    {
        $media = $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');

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

    /**
     * @param object           $entity
     * @param ProductAttribute $attribute
     * @param string           $locale
     * @param string           $scope
     *
     * @return ProductValue
     */
    protected function getValueMock($entity, $attribute, $locale = null, $scope = null)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $value->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($scope));

        return $value;
    }

    /**
     * @param string $identifier
     *
     * @return Product
     */
    protected function getProductMock($identifier)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue($identifier));

        return $product;
    }

    /**
     * @param string  $code
     * @param boolean $localisable
     * @param boolean $scopable
     *
     * @return ProductAttribute
     */
    protected function getAttributeMock($code, $localisable = false, $scopable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($localisable));

        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));

        return $attribute;
    }
}
