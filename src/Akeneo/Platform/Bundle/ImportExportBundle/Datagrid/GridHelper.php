<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class GridHelper
{
    private SecurityFacade $securityFacade;
    private array $jobSecurityMapping;

    public function __construct(SecurityFacade $securityFacade, array $jobSecurityMapping)
    {
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
    }

    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $jobExecutionType = $record->getValue('type');
            $canViewDetail = true;
            if (array_key_exists($jobExecutionType, $this->jobSecurityMapping)) {
                $canViewDetail = $this->securityFacade->isGranted($this->jobSecurityMapping[$jobExecutionType]);
            }

            return [
                'view' => $canViewDetail,
            ];
        };
    }
}
