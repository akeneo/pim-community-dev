<?php

namespace spec\Pim\Bundle\DataGridBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Datagrid\Common;
use Pim\Bundle\CatalogBundle\Entity;

class HideColumnsListenerSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $security,
        TokenInterface $token,
        UserInterface $user,
        EntityRepository $datagridConfigRepo,
        BuildAfter $event,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        Common\DatagridConfiguration $config
    ) {
        $security->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $acceptor->getConfig()->willReturn($config);
        $config->offsetGetByPath('[name]')->willReturn('foobar-grid');

        $this->beConstructedWith($security, $datagridConfigRepo);
    }

    function it_hides_uneeded_columns(
        Entity\DatagridConfiguration $datagridConfig,
        $config,
        $event,
        $datagridConfigRepo,
        $user
    ) {
        $datagridConfigRepo->findOneBy([
            'datagridAlias' => 'foobar-grid',
            'user'          => $user,
        ])->willReturn($datagridConfig);

        $datagridConfig->getColumns()->willReturn(['sku', 'description']);

        $config->offsetGetByPath('[columns]')->willReturn([
            'sku'         => ['label' => 'SKU'],
            'name'        => ['label' => 'Name'],
            'description' => ['label' => 'Description'],
        ]);

        $config->offsetGetByPath('[sorters][columns][sku]')->willReturn('sku_sorter_config');
        $config->offsetGetByPath('[sorters][columns][description]')->willReturn('description_sorter_config');

        $config->offsetGetByPath('[sorters][columns]')->willReturn([
            'sku'         => 'sku_sorter_config',
            'description' => 'description_sorter_config',
        ]);

        $config->offsetSetByPath('[availableColumns]', [
            'sku'         => 'SKU',
            'name'        => 'Name',
            'description' => 'Description',
        ])->shouldBeCalled();

        $config->offsetSetByPath('[columns]', [
            'sku'         => ['label' => 'SKU'],
            'description' => ['label' => 'Description'],
        ])->shouldBeCalled();

        $config->offsetSetByPath('[sorters][columns]', [
            'sku'         => 'sku_sorter_config',
            'description' => 'description_sorter_config',
        ])->shouldBeCalled();

        $this->onBuildAfter($event);
    }

    function it_sorts_columns_by_the_order_specified_by_the_user(
        Entity\DatagridConfiguration $datagridConfig,
        $config,
        $event,
        $datagridConfigRepo,
        $user
    ) {
        $datagridConfigRepo->findOneBy([
            'datagridAlias' => 'foobar-grid',
            'user'          => $user,
        ])->willReturn($datagridConfig);

        $datagridConfig->getColumns()->willReturn(['name', 'description', 'sku']);

        $config->offsetGetByPath('[columns]')->willReturn([
            'sku'         => ['label' => 'SKU'],
            'name'        => ['label' => 'Name'],
            'description' => ['label' => 'Description'],
        ]);

        $config->offsetGetByPath('[sorters][columns][sku]')->willReturn('sku_sorter_config');
        $config->offsetGetByPath('[sorters][columns][name]')->willReturn('name_sorter_config');
        $config->offsetGetByPath('[sorters][columns][description]')->willReturn('description_sorter_config');

        $config->offsetGetByPath('[sorters][columns]')->willReturn([
            'sku'         => 'sku_sorter_config',
            'description' => 'description_sorter_config',
        ]);

        $config->offsetSetByPath('[availableColumns]', [
            'sku'         => 'SKU',
            'name'        => 'Name',
            'description' => 'Description',
        ])->shouldBeCalled();

        $config->offsetSetByPath('[columns]', [
            'name'        => ['label' => 'Name'],
            'description' => ['label' => 'Description'],
            'sku'         => ['label' => 'SKU'],
        ])->shouldBeCalled();

        $config->offsetSetByPath('[sorters][columns]', [
            'sku'         => 'sku_sorter_config',
            'name'        => 'name_sorter_config',
            'description' => 'description_sorter_config',
        ])->shouldBeCalled();

        $this->onBuildAfter($event);
    }

    function it_keeps_all_columns_and_sorters_if_no_datagrid_configuration_is_set(
        $datagridConfigRepo,
        $user,
        $config,
        $event
    ) {
        $datagridConfigRepo->findOneBy([
            'datagridAlias' => 'foobar-grid',
            'user'          => $user,
        ])->willReturn(null);

        $config->offsetGetByPath('[columns]')->willReturn([
            'sku'         => ['label' => 'SKU'],
            'name'        => ['label' => 'Name'],
            'description' => ['label' => 'Description'],
        ]);

        $config->offsetSetByPath('[availableColumns]', [
            'sku'         => 'SKU',
            'name'        => 'Name',
            'description' => 'Description',
        ])->shouldBeCalled();

        $config->offsetSetByPath('[columns]', Argument::any())->shouldNotBeCalled();
        $config->offsetSetByPath('[sorters][columns]', Argument::any())->shouldNotBeCalled();

        $this->onBuildAfter($event);
    }
}
