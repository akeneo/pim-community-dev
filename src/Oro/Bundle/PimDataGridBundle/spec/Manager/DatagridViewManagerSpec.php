<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager;
use PhpSpec\ObjectBehavior;

class DatagridViewManagerSpec extends ObjectBehavior
{
    function let(
        EntityRepository $repository,
        DatagridManager $manager
    ) {
        $this->beConstructedWith($repository, $manager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewManager::class);
    }
}
