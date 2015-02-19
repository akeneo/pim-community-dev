<?php

namespace spec\Pim\Bundle\DataGridBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;

class DatagridViewManagerSpec extends ObjectBehavior
{
    function let(
        EntityRepository $repository,
        DatagridManager $manager,
        ObjectManager $objectManager
    ) {
        $this->beConstructedWith($repository, $manager, $objectManager);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_throws_exception_when_save_anything_else_than_a_view()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\DataGridBundle\Entity\DatagridView, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_a_view()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\DataGridBundle\Entity\DatagridView, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
