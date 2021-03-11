<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\MassAction;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Mass action handler for the rules data grid in order to launch several rules using a backend process
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ExecuteMassActionHandler implements MassActionHandlerInterface
{
    protected const RULE_EXECUTION_JOB = 'rule_engine_execute_rules';

    protected JobLauncherInterface $jobLauncher;
    protected TokenStorageInterface $tokenStorage;
    protected IdentifiableObjectRepositoryInterface $jobInstanceRepo;

    public function __construct(
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepo = $jobInstanceRepo;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction): MassActionResponseInterface
    {
        $results = $datagrid->getDatasource()->getResults();
        $rules = [];

        foreach ($results as $result) {
            $rules[] = $result->getValue('code');
        }

        if (empty($rules)) {
            return new MassActionResponse(
                false,
                'pimee_catalog_rule.datagrid.rule-grid.mass_action.execute.empty_selection'
            );
        }

        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(static::RULE_EXECUTION_JOB);
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = [
            'rule_codes' => $rules,
            'user_to_notify' => $user->getUsername(),
        ];
        $this->jobLauncher->launch($jobInstance, $user, $configuration);

        return new MassActionResponse(true, 'pimee_catalog_rule.datagrid.rule-grid.mass_action.execute.success');
    }
}
