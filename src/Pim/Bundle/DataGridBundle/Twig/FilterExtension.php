<?php

namespace Pim\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Add some functions about datagrid filters
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends Twig_Extension
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('filter_label', [$this, 'filterLabel']),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function filterLabel($code)
    {
        $configuration = $this->getDatagridManager()->getDatagrid('product-grid')->getAcceptor()->getConfig();
        $this->getFiltersConfigurator()->configure($configuration);

        $label = $configuration->offsetGetByPath(sprintf('[filters][columns][%s][label]', $code));

        if (null === $label) {
            throw new \LogicException(sprintf('Attribute "%s" does not exists', $code));
        }

        $label = $this->container->get('translator')->trans($label);

        return $label;
    }

    /**
     * @return Manager
     */
    final protected function getDatagridManager()
    {
        return $this->container->get('oro_datagrid.datagrid.manager');
    }

    /**
     * @return FiltersConfigurator
     */
    final protected function getFiltersConfigurator()
    {
        return $this->container->get('pim_datagrid.datagrid.configuration.product.filters_configurator');
    }
}
