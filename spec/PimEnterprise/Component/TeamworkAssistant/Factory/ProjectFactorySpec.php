<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\DatagridViewTypes;
use PimEnterprise\Component\TeamworkAssistant\Factory\ProjectFactory;
use PimEnterprise\Component\TeamworkAssistant\Factory\ProjectFactoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectFactorySpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $datagridViewFactory,
        ObjectUpdaterInterface $projectUpdater,
        ObjectUpdaterInterface $datagridViewUpdater
    ) {
        $this->beConstructedWith(
            $projectUpdater,
            $datagridViewFactory,
            $datagridViewUpdater,
            'PimEnterprise\Component\TeamworkAssistant\Model\Project'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFactory::class);
    }

    function it_is_a_project_factory()
    {
        $this->shouldImplement(ProjectFactoryInterface::class);
    }

    function it_creates_a_project_object(
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

        $projectUpdater->update(Argument::type(ProjectInterface::class), [
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

        $this->create([
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
        ])->shouldHaveType(ProjectInterface::class);
    }
}
