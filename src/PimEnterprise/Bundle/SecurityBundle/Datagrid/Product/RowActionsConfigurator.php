<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Datagrid\Product;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Akeneo\Pim\Permission\Component\Attributes;
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

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /**
     * @param ConfigurationRegistry                 $registry
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param LocaleRepositoryInterface             $localeRepository
     * @param IdentifiableObjectRepositoryInterface       $productModelRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository = null
    ) {
        $this->registry = $registry;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->localeRepository = $localeRepository;
        $this->productModelRepository = $productModelRepository;
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
            $product = $this->getProductRepository($record)->findOneByIdentifier($record->getValue('identifier'));
            $editGranted = $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
            $ownershipGranted = $editGranted && $this->authorizationChecker->isGranted(Attributes::OWN, $product);

            return [
                'show'            => !$editGranted,
                'edit'            => $editGranted,
                'edit_categories' => $ownershipGranted,
                'delete'          => $ownershipGranted,
                'toggle_status'   => $ownershipGranted
            ];
        };
    }

    /**
     * Returns the repository for a product or product model
     *
     * @param ResultRecordInterface $record
     *
     * @return IdentifiableObjectRepositoryInterface
     */
    private function getProductRepository(ResultRecordInterface $record): IdentifiableObjectRepositoryInterface
    {
        $productType = $record->getValue('document_type');

        if (self::PRODUCT_MODEL_TYPE === $productType) {
            return $this->productModelRepository;
        }

        return $this->productRepository;
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
     * Get row action configuration
     *
     * @return array
     */
    protected function getActionConfiguration()
    {
        return $this->actionConfiguration;
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
