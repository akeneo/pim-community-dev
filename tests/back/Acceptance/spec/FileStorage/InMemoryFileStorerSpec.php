<?php
declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\FileStorage;

use Akeneo\Test\Acceptance\FileStorage\InMemoryFileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;

class InMemoryFileStorerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryFileStorer::class);
    }

    function it_stores_the_file(\SplFileInfo $rawFile)
    {
        $rawFile->getPathname()->willReturn('/path/image.jpg');
        $rawFile->getFilename()->willReturn('image.jpg');

        $file = $this->store($rawFile, 'catalogStorage');

        $file->shouldImplement(FileInfoInterface::class);
        $file->getKey()->shouldReturn('/path/image.jpg');
        $file->getOriginalFilename()->shouldReturn('image.jpg');
    }
}
