<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Pim\Bundle\ImportExportBundle\Writer\FileWriter;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterTest extends \PHPUnit_Framework_TestCase
{
    const EXPORT_DIRECTORY = '/tmp';
    const EXPORT_FILE = 'test';
    const EXPECT_PATH = '/tmp/constat';

    protected function tearDown()
    {
        $filename=sprintf('%s/%s', self::EXPORT_DIRECTORY, self::EXPORT_FILE);
        @unlink(self::EXPECT_PATH);
        @unlink($filename);
    }

    public function testWrite()
    {
        file_put_contents(self::EXPECT_PATH, 'foo');
        $writer = new FileWriter();
        $writer->setDirectoryName(self::EXPORT_DIRECTORY);
        $writer->setFileName(self::EXPORT_FILE);
        $writer->write(array('foo'));
        $filename=sprintf('%s/%s', self::EXPORT_DIRECTORY, self::EXPORT_FILE);
        $this->assertFileExists($filename);
        $this->assertFileEquals(self::EXPECT_PATH, $filename);
    }
}
