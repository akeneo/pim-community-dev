<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Updater\DatagridViewUpdater;
use Akeneo\UserManagement\Component\Model\UserInterface;

class DatagridViewUpdaterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $userRepository)
    {
        $this->beConstructedWith($userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewUpdater::class);
    }

    function it_is_a_object_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_datagrid()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                DatagridView::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_the_data_grid_property($userRepository, DatagridView $datagridView, UserInterface $user)
    {
        $userRepository->findOneByIdentifier('julia')->willreturn($user);

        $datagridView->setLabel('My view')->shouldBeCalled();
        $datagridView->setOwner($user)->shouldBeCalled();
        $datagridView->setType(DatagridView::TYPE_PUBLIC)->shouldBeCalled();
        $datagridView->setDatagridAlias('product-grid')->shouldBeCalled();
        $datagridView->setColumns(['name', 'price'])->shouldBeCalled();
        $datagridView->setFilters('my filter as string')->shouldBeCalled();

        $this->update($datagridView, [
            'owner' => 'julia',
            'type' => DatagridView::TYPE_PUBLIC,
            'datagrid_alias' => 'product-grid',
            'label' => 'My view',
            'columns' => 'name, price',
            'filters' => 'my filter as string',
        ])->shouldReturn($this);
    }
}
