<?php

namespace spec\Oro\Bundle\PimDataGridBundle\DataTransformer;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class DefaultViewDataTransformerSpec extends ObjectBehavior
{
    function let(DatagridViewRepositoryInterface $datagridViewRepo)
    {
        $this->beConstructedWith($datagridViewRepo);
    }

    function it_transforms_the_given_user($datagridViewRepo, UserInterface $julia, DatagridView $productView)
    {
        $datagridViewRepo->getDatagridViewTypeByUser($julia)->willReturn([['datagridAlias' => 'product-grid'], ['datagridAlias' => 'category']]);

        $julia->getDefaultGridView('product-grid')->willReturn($productView);
        $julia->getDefaultGridView('category')->willReturn(null);

        $this->transform($julia)->shouldReturn($julia);
    }
}
