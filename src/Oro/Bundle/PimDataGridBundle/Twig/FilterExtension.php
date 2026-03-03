<?php

namespace Oro\Bundle\PimDataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Add some functions about datagrid filters
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends AbstractExtension
{
    private Manager $datagridManager;
    private ConfiguratorInterface $filtersConfigurator;
    private TranslatorInterface $translator;

    public function __construct(
        Manager               $datagridManager,
        ConfiguratorInterface $filtersConfigurator,
        TranslatorInterface   $translator
    ) {
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
            new TwigFunction('filter_label', [$this, 'filterLabel']),
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

        return $this->translator->trans($label);
    }
}
