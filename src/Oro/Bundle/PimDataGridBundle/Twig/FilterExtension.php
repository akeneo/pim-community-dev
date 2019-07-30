<?php

namespace Oro\Bundle\PimDataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
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
    /** @var Manager */
    private $datagridManager;

    /** @var ConfiguratorInterface */
    private $filtersConfigurator;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(Manager $datagridManager, ConfiguratorInterface $filtersConfigurator, TranslatorInterface $translator)
    {
        $this->datagridManager = $datagridManager;
        $this->filtersConfigurator = $filtersConfigurator;
        $this->translator = $translator;
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
        $configuration = $this->datagridManager->getDatagrid('product-grid')->getAcceptor()->getConfig();
        $this->filtersConfigurator->configure($configuration);

        $label = $configuration->offsetGetByPath(sprintf('[filters][columns][%s][label]', $code));

        if (null === $label) {
            return null;
        }

        $label = $this->translator->trans($label);

        return $label;
    }
}
