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
        $writer = new FileWriter(self::EXPORT_PATH);
        $writer->write(array('foo'));

        $this->assertFileExists(self::EXPORT_PATH);
        $this->assertFileEquals(self::EXPECT_PATH, self::EXPORT_PATH);
    }
}
