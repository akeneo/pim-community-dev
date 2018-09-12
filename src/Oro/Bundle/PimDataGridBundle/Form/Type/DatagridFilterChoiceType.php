<?php

namespace Oro\Bundle\PimDataGridBundle\Form\Type;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DatagridFilterChoiceType
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridFilterChoiceType extends AbstractType
{
    /** @var Manager */
    protected $manager;

    /** @var FiltersConfigurator */
    protected $configurator;

    /** @var string */
    protected $datagrid;

    /** @var array */
    protected $disallowed = ['scope', 'locale'];

    /**
     * @param Manager             $manager
     * @param FiltersConfigurator $configurator
     * @param string              $datagrid
     */
    public function __construct(Manager $manager, FiltersConfigurator $configurator, $datagrid)
    {
        $this->manager = $manager;
        $this->configurator = $configurator;
        $this->datagrid = $datagrid;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $configuration = $this->manager->getDatagrid($this->datagrid)->getAcceptor()->getConfig();
        $this->configurator->configure($configuration);

        $attributes = $configuration->offsetGetByPath('[filters][columns]');
        $configs = $configuration->offsetGetByPath(sprintf(
            ConfiguratorInterface::SOURCE_PATH,
            ConfiguratorInterface::USEABLE_ATTRIBUTES_KEY
        ));
        $choices = [];

        foreach ($attributes as $code => $filter) {
            if (in_array($code, $this->disallowed)) {
                continue;
            }

            $group = 'System';

            if (isset($configs[$code])) {
                $group = $configs[$code]['group'];
            }

            $choices[$group][$code] = $filter['label'];
        }

        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_datagrid_product_filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
