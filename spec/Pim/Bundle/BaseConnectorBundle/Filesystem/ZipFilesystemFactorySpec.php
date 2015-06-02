<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Filesystem;

use PhpSpec\ObjectBehavior;

class ZipFilesystemFactorySpec extends ObjectBehavior
{
    function it_creates_a_zip()
    {
        $fileSystem = $this->createZip('../FileSystem/');

        $fileSystem->shouldBeAnInstanceOf('\Gaufrette\Filesystem');
        $fileSystem->getAdapter()->shouldBeAnInstanceOf('\Gaufrette\Adapter\Zip');
    }
}
