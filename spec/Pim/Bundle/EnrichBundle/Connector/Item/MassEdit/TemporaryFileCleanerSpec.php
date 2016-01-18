<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Item\MassEdit;

use PhpSpec\ObjectBehavior;

class TemporaryFileCleanerSpec extends ObjectBehavior
{
    function it_removes_temporary_files()
    {
        fopen("/tmp/testfile.txt", "w");
        $configuration = ['actions' => [['value' => ['filePath' => '/tmp/testfile.txt']]]];
        $this->execute($configuration);
        assert(!file_exists('/tmp/testfile.txt'));
    }
}
