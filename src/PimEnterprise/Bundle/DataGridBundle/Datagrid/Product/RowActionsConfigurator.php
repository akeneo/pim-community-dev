<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Symfony\Component\Security\Core\SecurityContextInterface;

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

    /**
     * @param ConfigurationRegistry    $registry the conf registry
     * @param SecurityContextInterface $securityContext the security context
     * @param CategoryRepository       $categoryRepository the category registry
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
        $this->checkEditActions();
    }

    /**
     * Check if the user has the permission to use the row actions.
     */
    protected function checkEditActions()
    {
        $path = sprintf('[source][%s]', ContextConfigurator::CURRENT_TREE_ID_KEY);
        $tree = $this->categoryRepository->find($this->configuration->offsetGetByPath($path));

        if (false === $this->securityContext->isGranted(CategoryVoter::EDIT_PRODUCTS, $tree)) {
            $this->removeRowAction('edit');
            $this->removeRowAction('edit_categories');
            $this->removeRowAction('delete');
            $this->removeProperty('edit_link');
            $this->removeProperty('delete_link');
            $this->addShowRowAction();
            $this->addShowLinkProperty();
        }
    }

    /**
     * Remove row actions from the configuration.
     *
     * @param string $action
     *
     * @return RowActionsConfigurator
     */
    protected function removeRowAction($action)
    {
        $actions = $this->configuration->offsetGet('actions');
        unset($actions[$action]);
        $this->configuration->offsetSet('actions', $actions);

        return $this;
    }

    /**
     * @param $property
     *
     * @return RowActionsConfigurator
     */
    protected function removeProperty($property)
    {
        $properties = $this->configuration->offsetGet('properties');
        unset($properties[$property]);
        $this->configuration->offsetSet('properties', $properties);

        return $this;
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
