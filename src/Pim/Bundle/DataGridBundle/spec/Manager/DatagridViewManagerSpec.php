<?php

namespace spec\Pim\Bundle\DataGridBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;

class DatagridViewManagerSpec extends ObjectBehavior
{
    function let(
        EntityRepository $repository,
        DatagridManager $manager,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith($repository, $manager, $saver, $remover);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_saves_a_view($saver, DatagridView $view)
    {
        $saver->save($view, [])->shouldBeCalled();
        $this->save($view);
    }

    function it_removes_a_view($remover, DatagridView $view)
    {
        $remover->remove($view, [])->shouldBeCalled();
        $this->remove($view);
    }
}
