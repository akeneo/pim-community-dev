<?php

namespace Pim\Bundle\DataGridBundle\Twig;

use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add some functions about datagrid filters
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends \Twig_Extension
{
    /** @var ContainerInterface */
    protected $container;

    /** @var FiltersConfigurator */
    protected $configurator;

    /**
     * @param ContainerInterface  $container
     * @param FiltersConfigurator $configurator
     *
     * @internal param ContainerInterface $configuration
     */
    public function __construct(ContainerInterface $container, FiltersConfigurator $configurator)
    {
        $this->container    = $container;
        $this->configurator = $configurator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'filter_label' => new \Twig_Function_Method($this, 'filterLabel'),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function filterLabel($code)
    {
        $manager = $this->container->get('oro_datagrid.datagrid.manager');
        $configuration = $manager->getDatagrid('product-grid')->getAcceptor()->getConfig();
        $this->configurator->configure($configuration);

        $label = $configuration->offsetGetByPath(sprintf('[filters][columns][%s][label]', $code));

        if (null === $label) {
            throw new \LogicException(sprintf('Attribute "%s" does not exists', $code));
        }

        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_datagrid_filter_extension';
    }
}
