<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Filesystem;

use PhpSpec\ObjectBehavior;

class ZipFilesystemFactorySpec extends ObjectBehavior
{
    function it_creates_a_zip()
    {
        $fileSystem = $this->createZip(__DIR__ . DIRECTORY_SEPARATOR . 'test.zip');

        $fileSystem->shouldBeAnInstanceOf('\League\Flysystem\Filesystem');
        $fileSystem->getAdapter()->shouldBeAnInstanceOf('\League\Flysystem\ZipArchive\ZipArchiveAdapter');
    }
}
