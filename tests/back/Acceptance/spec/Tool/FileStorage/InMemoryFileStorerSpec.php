<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Test\Acceptance\Tool\FileStorage\InMemoryFileStorer;
use PhpSpec\ObjectBehavior;

class InMemoryFileStorerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryFileStorer::class);
    }

    function it_store_the_file()
    {
        $rawFile = new \SplFileInfo('/path/image.jpg');
        $file = new FileInfo();
        $file->setKey($rawFile->getPathname());
        $file->setOriginalFilename($rawFile->getFilename());

        $this->store($rawFile, 'catalogStorage')->shouldReturn($file);
    }
}
