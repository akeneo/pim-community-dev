<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Filesystem;

use Pim\Bundle\ImportExportBundle\Filesystem\ZipFilesystemFactory;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ZipFilesystemFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->factory = new ZipFilesystemFactory();
    }

    public function tearDown()
    {
        @unlink('/tmp/foobar.zip');
    }

    public function testCreateZip()
    {
        $fs = $this->factory->createZip('/tmp/foobar.zip');

        $this->assertInstanceOf('Gaufrette\Filesystem', $fs);
        $this->assertAttributeInstanceOf('Gaufrette\Adapter\Zip', 'adapter', $fs);
    }
}
