<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Media;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Media
     */
    protected $media;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->media = new Media();
    }

    /**
     * Test related property
     */
    public function testId()
    {
        $this->assertNull($this->media->getId());

        $id = 5;
        $this->media->setId($id);
        $this->assertEquals($id, $this->media->getId());
    }

    /**
     * Test related property
     */
    public function testFile()
    {
        $this->assertNull($this->media->getFile());

        $fileMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $this->media->setFile($fileMock);
        $this->assertEquals($fileMock, $this->media->getFile());
    }

    /**
     * Test related property
     */
    public function testFilename()
    {
        $this->assertNull($this->media->getFilename());

        $filename = 'test-filename';
        $this->media->setFilename($filename);
        $this->assertEquals($filename, $this->media->getFilename());
    }

    /**
     * Test related property
     */
    public function testFilePath()
    {
        $this->assertNull($this->media->getFilePath());

        $filePath = 'bapflexible/uploads/test-file.txt';
        $this->media->setFilePath($filePath);
        $this->assertEquals($filePath, $this->media->getFilePath());
    }

    /**
     * Test related property
     */
    public function testMimeType()
    {
        $this->assertNull($this->media->getMimeType());

        $mimeType = 'image/png';
        $this->media->setMimeType($mimeType);
        $this->assertEquals($mimeType, $this->media->getMimeType());
    }
}
