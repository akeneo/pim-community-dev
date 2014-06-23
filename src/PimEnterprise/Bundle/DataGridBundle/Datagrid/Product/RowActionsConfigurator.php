<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

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

    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var array */
    protected $actionConfiguration = [];

    /**
     * @param ConfigurationRegistry    $registry
     * @param SecurityContextInterface $securityContext
     * @param CategoryRepository       $categoryRepository
     */
    public function __construct(
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        CategoryRepository $categoryRepository
    ) {
        $this->registry = $registry;
        $this->securityContext = $securityContext;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        $this->configuration = $configuration;
        $this->addDispatchAction();
        $this->checkEditActions();
    }

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
     * Check if the user has the permission to use the row actions.
     *
     * @return null
     */
    protected function checkEditActions()
    {
        $this->addShowRowAction();
        $this->addShowLinkProperty();

        $path = sprintf('[source][%s]', ContextConfigurator::CURRENT_TREE_ID_KEY);
        $tree = $this->categoryRepository->find($this->configuration->offsetGetByPath($path));

        if ($this->securityContext->isGranted(CategoryVoter::EDIT_PRODUCTS, $tree)) {
            $this->actionConfiguration = ['show' => false];
        } else {
            $this->actionConfiguration = [
                'edit'            => false,
                'edit_categories' => false,
                'delete'          => false,
            ];
        }

        $this->configuration->offsetSetByPath(
            sprintf('[%s]', ActionExtension::ACTION_CONFIGURATION_KEY),
            function () {
                return $this->getActionConfiguration();
            }
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
