<?php

namespace spec\Pim\Bundle\DataGridBundle\DataTransformer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;


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
