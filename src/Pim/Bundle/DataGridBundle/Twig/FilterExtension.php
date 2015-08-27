<?php

namespace Pim\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;

/**
 * Add some functions about datagrid filters
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends \Twig_Extension
{
    /** @var Manager */
    protected $manager;

    /** @var FiltersConfigurator */
    protected $configurator;

    /**
     * @param Manager             $configuration
     * @param FiltersConfigurator $configurator
     */
    public function __construct(Manager $manager, FiltersConfigurator $configurator)
    {
        $this->manager      = $manager;
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
        $configuration = $this->manager->getDatagrid('product-grid')->getAcceptor()->getConfig();
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
