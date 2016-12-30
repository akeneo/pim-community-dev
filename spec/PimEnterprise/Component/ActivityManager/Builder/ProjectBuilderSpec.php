<?php

namespace spec\PimEnterprise\Component\ActivityManager\Builder;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\DatagridViewTypes;
use PimEnterprise\Component\ActivityManager\Builder\ProjectBuilder;
use PimEnterprise\Component\ActivityManager\Builder\ProjectBuilderInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectBuilderSpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $datagridViewFactory,
        SimpleFactoryInterface $projectFactory,
        ObjectUpdaterInterface $projectUpdater,
        ObjectUpdaterInterface $datagridViewUpdater
    ) {
        $this->beConstructedWith($projectFactory, $projectUpdater, $datagridViewFactory, $datagridViewUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectBuilder::class);
    }

    function it_is_a_project_builder()
    {
        $this->shouldImplement(ProjectBuilderInterface::class);
    }

    function it_builds_a_project_object(
        $projectFactory,
        $projectUpdater,
        $datagridViewFactory,
        $datagridViewUpdater,
        ProjectInterface $project,
        DatagridView $datagridView,
        UserInterface $user
    ) {
        $datagridViewFactory->create()->willReturn($datagridView);
        $datagridViewUpdater->update($datagridView, [
            'filters' => 'i=1&p=10&s%5Bupdated',
            'columns' => 'a:1{blublublu...}',
            'type' => DatagridViewTypes::PROJECT_VIEW,
            'owner' => $user,
            'datagrid_alias' => 'product-grid',
        ])->shouldBeCalled();

        $projectFactory->create()->willReturn($project);
        $projectUpdater->update($project, [
            'label' => 'Summer collection 2017',
            'due_date' => '2016-12-15',
            'description' => 'My description',
            'datagrid_view' => $datagridView,
            'product_filters' => [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['mugs'],
                'context' => ['locale' => 'en_US','scope' => 'ecommerce']
            ],
            'channel' => 'ecommerce',
            'locale' => 'fr_FR',
            'owner' => $user,
        ])->shouldBeCalled();

        $this->build([
            'label' => 'Summer collection 2017',
            'due_date' => '2016-12-15',
            'description' => 'My description',
            'datagrid_view' => [
                'filters' => 'i=1&p=10&s%5Bupdated',
                'columns' => 'a:1{blublublu...}',
            ],
            'product_filters' => [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['mugs'],
                'context' => ['locale' => 'en_US','scope' => 'ecommerce']
            ],
            'channel' => 'ecommerce',
            'locale' => 'fr_FR',
            'owner' => $user,
        ])->shouldReturn($project);
    }
}
