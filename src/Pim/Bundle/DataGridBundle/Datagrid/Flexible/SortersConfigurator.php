<?php

namespace Pim\Bundle\DataGridBundle\Datagrid\Flexible;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Sorters configurator for flexible grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SortersConfigurator implements ConfiguratorInterface
{
    /**
     * @param DatagridConfiguration
     */
    protected $configuration;

    /**
     * @param ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @param DatagridConfiguration $configuration   the grid config
     * @param ConfigurationRegistry $registry        the conf registry
     * @param FlexibleManager       $flexibleManager flexible manager
     */
    public function __construct(
        DatagridConfiguration $configuration,
        ConfigurationRegistry $registry,
        FlexibleManager $manager
    ) {
        $this->configuration   = $configuration;
        $this->registry        = $registry;
        $this->flexibleManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $attributes = $this->configuration->offsetGetByPath(OrmDatasource::USEABLE_ATTRIBUTES_PATH);
        $callback = $this->getApplyCallback();
        $columns = $this->configuration->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );
        foreach ($attributes as $attributeCode => $attribute) {
            $attributeType     = $attribute['attributeType'];
            $attributeTypeConf = $this->registry->getConfiguration($attributeType);
            $columnExists      = isset($columns[$attributeCode]);

            if (!$attributeTypeConf || !isset($attributeTypeConf['column'])) {
                throw new \LogicException(
                    sprintf(
                        'Attribute type %s must be configured to display sorter for attribute %s',
                        $attributeType,
                        $attributeCode
                    )
                );
            }

            if ($columnExists && $attributeTypeConf && $attributeTypeConf['column']) {

                if (!array_key_exists('sorter', $attributeTypeConf) || $attributeTypeConf['sorter'] !== null) {
                    $this->configuration->offsetSetByPath(
                        sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, $attributeCode),
                        array(
                            PropertyInterface::DATA_NAME_KEY => $attributeCode,
                            'apply_callback'                 => $callback
                        )
                    );
                }
            }
        }
    }

    /**
     * Creates sorter apply callback
     *
     * @return callable
     */
    protected function getApplyCallback()
    {
        $flexManager = $this->flexibleManager;

        return function (OrmDatasource $datasource, $attributeCode, $direction) use ($flexManager) {
            $qb = $datasource->getQueryBuilder();

            /** @var $entityRepository FlexibleEntityRepository */
            $entityRepository = $flexManager->getFlexibleRepository();
            $entityRepository->applySorterByAttribute($qb, $attributeCode, $direction);
        };
    }
}
