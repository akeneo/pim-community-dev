<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GridHelperSpec extends ObjectBehavior
{
    public function let(SecurityFacade $securityFacade)
    {
        $this->beConstructedWith($securityFacade, [
            'import' => 'pim_importexport_import_execution_show',
            'export' => 'pim_importexport_export_execution_show'
        ]);
    }

    public function it_enables_view_action_when_user_have_permission_to_view_job(
        SecurityFacade $securityFacade,
        ResultRecordInterface $record
    ) {
        $actionConfigurationClosure = $this->getActionConfigurationClosure();
        $record->getValue('type')->willReturn('export');

        $securityFacade->isGranted('pim_importexport_export_execution_show')->shouldBeCalled()->willReturn(true);
        $actionConfigurationClosure($record)->shouldReturn([
            'view' => true
        ]);
    }

    public function it_disables_view_action_when_user_does_not_have_permission_to_view_job(
        SecurityFacade $securityFacade,
        ResultRecordInterface $record
    ) {
        $actionConfigurationClosure = $this->getActionConfigurationClosure();
        $record->getValue('type')->willReturn('export');

        $securityFacade->isGranted('pim_importexport_export_execution_show')->shouldBeCalled()->willReturn(false);
        $actionConfigurationClosure($record)->shouldReturn([
            'view' => false
        ]);
    }

    public function it_enables_view_action_when_there_is_no_acl_related_to_the_job_type(
        SecurityFacade $securityFacade,
        ResultRecordInterface $record
    ) {
        $actionConfigurationClosure = $this->getActionConfigurationClosure();
        $record->getValue('type')->willReturn('quick_export');

        $securityFacade->isGranted(Argument::any())->shouldNotBeCalled();
        $actionConfigurationClosure($record)->shouldReturn([
            'view' => true
        ]);
    }
}
