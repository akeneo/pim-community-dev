<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\MassAction;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExecuteMassActionHandlerSpec extends ObjectBehavior
{
    function let(
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        JobInstance $executeRulesJob,
        TokenInterface $token
    ) {
        $jobInstanceRepo->findOneByIdentifier('rule_engine_execute_rules')->willReturn($executeRulesJob);
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($jobLauncher, $tokenStorage, $jobInstanceRepo);
    }

    function it_is_a_mass_action_handler()
    {
        $this->shouldHaveType(MassActionHandlerInterface::class);
    }

    function it_handles_datagrid_results(
        JobLauncherInterface $jobLauncher,
        TokenInterface $token,
        JobInstance $executeRulesJob,
        DatagridInterface $datagrid,
        MassActionInterface $massAction,
        DatasourceInterface $datasource,
        ResultRecordInterface $rule1,
        ResultRecordInterface $rule2,
        UserInterface $johndoe
    ) {
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getResults()->willReturn([$rule1, $rule2]);

        $rule1->getValue('code')->willReturn('first_rule');
        $rule2->getValue('code')->willReturn('second_rule');

        $token->getUser()->shouldBeCalled()->willReturn($johndoe);
        $johndoe->getUsername()->willReturn('johndoe');

        $jobLauncher->launch($executeRulesJob, $johndoe, [
            'rule_codes' => ['first_rule', 'second_rule'],
            'user_to_notify' => 'johndoe',
        ])->shouldBeCalled()->willReturn(new JobExecution());

        $this->handle($datagrid, $massAction)->shouldReturnAnInstanceOf(MassActionResponseInterface::class);
    }

    function it_doesnt_handle_empty_results(
        JobLauncherInterface $jobLauncher,
        DatagridInterface $datagrid,
        MassActionInterface $massAction,
        DatasourceInterface $datasource
    ) {
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getResults()->willReturn([]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->handle($datagrid, $massAction)->shouldReturnAnInstanceOf(MassActionResponseInterface::class);
    }
}
