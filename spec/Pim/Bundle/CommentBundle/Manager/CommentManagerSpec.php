<?php

namespace spec\Pim\Bundle\CommentBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface;

class CommentManagerSpec extends ObjectBehavior
{
    function let(
        CommentRepositoryInterface $repository,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith($repository, $objectManager);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_throws_exception_when_save_anything_else_than_a_comment()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a use Pim\Bundle\CommentBundle\Model\CommentInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_a_comment()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a use Pim\Bundle\CommentBundle\Model\CommentInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
