<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Row actions configurator for product grid
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RowActionsConfigurator implements ConfiguratorInterface
{
    /** @var DatagridConfiguration */
    protected $configuration;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param ConfigurationRegistry      $registry
     * @param SecurityContextInterface   $securityContext
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        ProductRepositoryInterface $productRepository
    ) {
        $this->registry = $registry;
        $this->securityContext = $securityContext;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addDispatchAction();
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
            $product = $this->productRepository->findOneBy(['id' => $record->getValue('id')]);
            if ($this->securityContext->isGranted(Attributes::EDIT_PRODUCT, $product)) {
                return [
                    'show' => false,
                    'edit' => true,
                    'edit_categories' => true,
                    'delete' => true,
                    'toggle_status' => true
                ];
            } else {
                return [
                    'show' => true,
                    'edit' => false,
                    'edit_categories' => false,
                    'delete' => false,
                    'toggle_status' => false
                ];
            }
        };
    }

    /**
     * Add a dispatch action to redirect on granted action (view or edit)
     *
     * @return null
     */
    protected function addDispatchAction()
    {
        $properties = $this->configuration->offsetGetByPath('[properties]');
        $properties['row_action_link']= [
            'type'  => 'url',
            'route' => 'pimee_enrich_product_dispatch',
            'params' => ['id', 'dataLocale']
        ];
        $this->configuration->offsetSetByPath('[properties]', $properties);

        $actions = $this->configuration->offsetGetByPath('[actions]');
        unset($actions['edit']['rowAction']);
        $actions['row_action']= [
            'type'      => 'tab-redirect',
            'label'     => 'Dispatch a product',
            'tab'       => 'attributes',
            'link'      => 'row_action_link',
            'rowAction' => true,
            'hidden'    => true
        ];
        $this->configuration->offsetSetByPath('[actions]', $actions);
    }

    /**
     * Add dynamic row action and configure the closure
     *
     * @return null
     */
    protected function addRowActions()
    {
        $this->addShowRowAction();
        $this->addShowLinkProperty();
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
        $viewAction = [];
        $viewAction['type'] = 'tab-redirect';
        $viewAction['label'] = 'View the product';
        $viewAction['tab'] = 'attributes';
        $viewAction['icon'] = 'eye-open';
        $viewAction['link'] = 'show_link';
        $viewAction['rowAction'] = true;

        $this->configuration->offsetSetByPath('[actions][show]', $viewAction);

        return $this;
    }

    /**
     * Add show link property to the configuration.
     *
     * @return RowActionsConfigurator
     */
    protected function addShowLinkProperty()
    {
        $showLink = [];
        $showLink['type'] = 'url';
        $showLink['route'] = 'pimee_enrich_product_show';
        $showLink['params'][] = 'id';

        $this->configuration->offsetSetByPath('[properties][show_link]', $showLink);

        return $this;
    }
}
