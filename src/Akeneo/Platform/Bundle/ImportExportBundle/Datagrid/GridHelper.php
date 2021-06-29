<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * This class is used by oro datagrid to decide which action is allowed in the product tracker grid.
 * Here we decide for each row if user can access to the job (there is a specific acl for import an export jobs details)
 */
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
