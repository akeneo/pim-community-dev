<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Authorization\Query\FetchUserRightsOnProductModel;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Row actions configurator for product grid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RowActionsConfigurator implements ConfiguratorInterface
{
    public const PRODUCT_MODEL_TYPE = 'product_model';

    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var FetchUserRightsOnProduct */
    protected $fetchUserRightsOnProduct;

    /** @var FetchUserRightsOnProductModel */
    protected $fetchUserRightsOnProductModel;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param ConfigurationRegistry         $registry
     * @param FetchUserRightsOnProduct      $fetchUserRightsOnProduct
     * @param FetchUserRightsOnProductModel $fetchUserRightsOnProductModel
     * @param UserContext                   $userContext
     */
    public function __construct(
        ConfigurationRegistry $registry,
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
        FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
        UserContext $userContext
    ) {
        $this->registry = $registry;
        $this->fetchUserRightsOnProduct = $fetchUserRightsOnProduct;
        $this->fetchUserRightsOnProductModel = $fetchUserRightsOnProductModel;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addCustomEditAction();
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
            if ($record->getValue('document_type') === self::PRODUCT_MODEL_TYPE) {
                return $this->getProductModelRights($record);
            } else {
                return $this->getProductRights($record);
            }
        };
    }

    protected function getProductModelRights(ResultRecordInterface $record): array
    {
        $user = $this->userContext->getUser();
        $userRight = $this->fetchUserRightsOnProductModel->fetch($record->getValue('identifier'), $user->getId());

        $test = [
            'show'            => !($userRight->canApplyDraftOnProductModel() || $userRight->isProductModelEditable()),
            'edit'            => $userRight->canApplyDraftOnProductModel() || $userRight->isProductModelEditable(),
            'edit_categories' => $userRight->isProductModelEditable(),
            'delete'          => $userRight->isProductModelEditable(),
            'toggle_status'   => $userRight->isProductModelEditable()
        ];

        return [
            'show'            => !($userRight->canApplyDraftOnProductModel() || $userRight->isProductModelEditable()),
            'edit'            => $userRight->canApplyDraftOnProductModel() || $userRight->isProductModelEditable(),
            'edit_categories' => $userRight->isProductModelEditable(),
            'delete'          => $userRight->isProductModelEditable(),
            'toggle_status'   => $userRight->isProductModelEditable()
        ];
    }

    protected function getProductRights(ResultRecordInterface $record): array
    {
        $user = $this->userContext->getUser();
        $userRight = $this->fetchUserRightsOnProduct->fetch($record->getValue('identifier'), $user->getId());

        return [
            'show'            => !($userRight->canApplyDraftOnProduct() || $userRight->isProductEditable()),
            'edit'            => $userRight->canApplyDraftOnProduct() || $userRight->isProductEditable(),
            'edit_categories' => $userRight->isProductEditable(),
            'delete'          => $userRight->isProductEditable(),
            'toggle_status'   => $userRight->isProductEditable()
        ];
    }

    /**
     * Add a custom edit action to redirect on granted action (view or edit)
     */
    protected function addCustomEditAction()
    {
        $actions = $this->configuration->offsetGetByPath('[actions]');
        unset($actions['edit']['rowAction']);
        $actions['row_action'] = [
            'launcherOptions' => [
                'className' => 'AknIconButton AknIconButton--small AknIconButton--edit'
            ],
            'type'      => 'navigate-product-and-product-model',
            'rowAction' => true,
        ];
        $this->configuration->offsetSetByPath('[actions]', $actions);
    }

    /**
     * Add dynamic row action and configure the closure
     */
    protected function addRowActions()
    {
        $this->addShowRowAction();
        $this->configuration->offsetSetByPath(
            '[action_configuration]',
            $this->getActionConfigurationClosure()
        );
    }

    /**
     * Add a show action to the configuration.
     *
     * @return RowActionsConfigurator
     */
    protected function addShowRowAction()
    {
        $viewAction = [
            'launcherOptions' => [
                'className' => 'AknIconButton AknIconButton--small AknIconButton--view'
            ],
            'type'            => 'navigate-product-and-product-model',
            'label'           => 'View the product',
            'rowAction'       => true,
        ];
        $this->configuration->offsetSetByPath('[actions][show]', $viewAction);

        return $this;
    }
}
