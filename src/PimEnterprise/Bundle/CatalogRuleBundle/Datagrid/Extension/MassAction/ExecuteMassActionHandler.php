<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Datagrid\Extension\MassAction;

use Akeneo\Component\Console\CommandLauncher;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Mass action handler for the rules data grid in order to launch several rules using a backend process
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class ExecuteMassActionHandler implements MassActionHandlerInterface
{
    /** @var CommandLauncher */
    protected $launcher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param CommandLauncher       $launcher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(CommandLauncher $launcher, TokenStorageInterface $tokenStorage)
    {
        $this->launcher = $launcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
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

        $this->launcher->executeBackground(sprintf(
            'akeneo:rule:run %s --username=%s',
            join(',', $rules),
            $this->tokenStorage->getToken()->getUsername()
        ));

        return new MassActionResponse(true, 'pimee_catalog_rule.datagrid.rule-grid.mass_action.execute.success');
    }
}
