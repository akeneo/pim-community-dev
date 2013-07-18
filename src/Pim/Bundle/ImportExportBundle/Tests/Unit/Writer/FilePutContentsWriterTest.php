<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Pim\Bundle\ImportExportBundle\Writer\FilePutContentsWriter;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilePutContentsWriterTest extends \PHPUnit_Framework_TestCase
{
    const EXPORT_PATH = '/tmp/export';
    const EXPECT_PATH = '/tmp/constat';

    protected function tearDown()
    {
        @unlink(self::EXPECT_PATH);
        @unlink(self::EXPORT_PATH);
    }

    public function testWrite()
    {
        file_put_contents(self::EXPECT_PATH, 'foo');
        $writer = new FilePutContentsWriter(self::EXPORT_PATH);
        $writer->write('foo');

        $this->assertFileExists(self::EXPORT_PATH);
        $this->assertFileEquals(self::EXPECT_PATH, self::EXPORT_PATH);
    }

    public function testWriteChunks()
    {
        file_put_contents(self::EXPECT_PATH, 'foobar');
        $writer = new FilePutContentsWriter(self::EXPORT_PATH);
        $writer->write('foo');
        $writer->write('bar');

        $this->assertFileExists(self::EXPORT_PATH);
        $this->assertFileEquals(self::EXPECT_PATH, self::EXPORT_PATH);
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\FileExistsException
     */
    public function testPathExists()
    {
        touch(self::EXPORT_PATH);
        new FilePutContentsWriter(self::EXPORT_PATH);
    }
}

