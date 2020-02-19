<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Datagrid\Configuration;

use Akeneo\Asset\Bundle\Doctrine\ORM\Query\FetchUserRightsOnAsset;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;

/**
 * Row actions configurator for product grid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RowActionsConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var FetchUserRightsOnAsset */
    protected $fetchUserRightsOnAsset;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param ConfigurationRegistry         $registry
     * @param FetchUserRightsOnAsset        $fetchUserRightsOnAsset
     * @param UserContext                   $userContext
     */
    public function __construct(
        ConfigurationRegistry $registry,
        FetchUserRightsOnAsset $fetchUserRightsOnAsset,
        UserContext $userContext
    ) {
        $this->registry = $registry;
        $this->fetchUserRightsOnAsset = $fetchUserRightsOnAsset;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addRowActions();
    }

    /**
     * Returns a callback to ease the configuration of different actions for each row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            return $this->getAssetRights($record);
        };
    }

    protected function getAssetRights(ResultRecordInterface $record): array
    {
        $user = $this->userContext->getUser();
        $userRight = $this->fetchUserRightsOnAsset->fetch($record->getValue('code'), $user->getId());

        return [
            'edit' => $userRight->isAssetEditable(),
            'view' => !$userRight->isAssetEditable()
        ];
    }

    /**
     * Add dynamic row action and configure the closure
     */
    protected function addRowActions()
    {
        $this->addDefaultClickRowAction();
        $this->configuration->offsetSetByPath('[action_configuration]', $this->getActionConfigurationClosure());
    }

    /**
     * Add a custom edit action to redirect on granted action (view or edit)
     */
    protected function addDefaultClickRowAction()
    {
        $actions = $this->configuration->offsetGetByPath('[actions]');

        unset($actions['edit']['rowAction']);

        $actions['row_action'] = [
            'launcherOptions' => [
                'className' => 'AknIconButton AknIconButton--small AknIconButton--edit'
            ],
            'type' => 'navigate',
            'link' => 'edit_link',
            'rowAction' => true,
        ];

        $this->configuration->offsetSetByPath('[actions]', $actions);
    }
}
