<?php

namespace spec\Akeneo\ActivityManager\Component\Writer;

use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Writer\Writer;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class WriterSpec extends ObjectBehavior
{
    function let(
        ProjectRepositoryInterface $projectRepository,
        EntityManagerInterface $entityManager,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $projectRepository,
            $entityManager,
            $objectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Writer::class);
    }

    function it_a_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }
}
