<?php

namespace spec\Pim\Bundle\CommentBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CommentBundle\Model\CommentInterface;
use Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface;

class CommentManagerSpec extends ObjectBehavior
{
    function let(
        CommentRepositoryInterface $repository,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith($repository, $saver, $remover);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_saves_a_comment($saver, CommentInterface $comment)
    {
        $saver->save($comment, [])->shouldBeCalled();
        $this->save($comment);
    }

    function it_removes_a_comment($remover, CommentInterface $comment)
    {
        $remover->remove($comment, [])->shouldBeCalled();
        $this->remove($comment);
    }
}
