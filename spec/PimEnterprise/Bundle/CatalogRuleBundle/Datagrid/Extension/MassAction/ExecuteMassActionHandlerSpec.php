<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\MassAction;

use Akeneo\Component\Console\CommandLauncher;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExecuteMassActionHandlerSpec extends ObjectBehavior
{
    function let(CommandLauncher $launcher, TokenStorageInterface $tokenStorage)
    {
        $this->beConstructedWith($launcher, $tokenStorage);
    }

    function it_is_a_mass_action_handler()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface');
    }

    function it_handles_datagrid_results(
        $launcher,
        $tokenStorage,
        TokenInterface $token,
        DatagridInterface $datagrid,
        MassActionInterface $massAction,
        DatasourceInterface $datasource,
        ResultRecordInterface $rule1,
        ResultRecordInterface $rule2
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUsername()->willReturn('doe');

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getResults()->willReturn([$rule1, $rule2]);

        $rule1->getValue('code')->willReturn('first_rule');
        $rule2->getValue('code')->willReturn('second_rule');

        $launcher->executeBackground('akeneo:rule:run first_rule,second_rule --username=doe')->shouldBeCalled();

        $this->handle($datagrid, $massAction)->shouldReturnAnInstanceOf(
            'Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface'
        );
    }

    function it_doesnt_handle_empty_results(
        $launcher,
        DatagridInterface $datagrid,
        MassActionInterface $massAction,
        DatasourceInterface $datasource
    ) {
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getResults()->willReturn([]);

        $launcher->executeBackground(Argument::any())->shouldNotBeCalled();

        $this->handle($datagrid, $massAction)->shouldReturnAnInstanceOf(
            'Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface'
        );
    }
}
