<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Cleaner;

use PhpSpec\ObjectBehavior;

class MassEditTemporaryFileCleanerSpec extends ObjectBehavior
{
    function it_remove_temporary_files()
    {
        fopen("/tmp/testfile.txt", "w");
        $configuration = ['actions' => [['value' => ['filePath' => '/tmp/testfile.txt']]]];
        $this->execute($configuration);
        assert(!file_exists('/tmp/testfile.txt'));
    }
}
