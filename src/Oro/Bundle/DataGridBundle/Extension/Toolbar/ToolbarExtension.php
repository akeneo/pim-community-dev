<?php

namespace Oro\Bundle\DataGridBundle\Extension\Toolbar;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

class ToolbarExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const METADATA_KEY = 'options';

    const OPTIONS_PATH = '[options]';
    const TOOLBAR_OPTION_PATH = '[options][toolbarOptions]';
    const PAGER_ITEMS_OPTION_PATH = '[options][toolbarOptions][pageSize][items]';
    const PAGER_DEFAULT_PER_PAGE_OPTION_PATH = '[options][toolbarOptions][pageSize][default_per_page]';

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $options = $config->offsetGetByPath(self::TOOLBAR_OPTION_PATH, []);
        // validate configuration and pass default values back to config
        $configuration = $this->validateConfiguration(new Configuration(), ['toolbarOptions' => $options]);
        $config->offsetSetByPath(sprintf('%s[%s]', self::OPTIONS_PATH, 'toolbarOptions'), $configuration);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        /**
         * Default toolbar options
         *  [
         *      'hide'       => false,
         *      'pageSize'   => [
         *          'hide'  => false,
         *          'items' => [10, 25, 50, 100],
         *          'default_per_page' => 10
         *       ],
         *      'pagination' => [
         *          'hide' => false,
         *      ]
         *  ];
         */

        $perPageDefault = $config->offsetGetByPath(self::PAGER_DEFAULT_PER_PAGE_OPTION_PATH);
        $pageSizeItems = $config->offsetGetByPath(self::PAGER_ITEMS_OPTION_PATH);

        $exist = array_filter(
            $pageSizeItems,
            function ($item) use ($perPageDefault) {
                if (is_array($item) && isset($item['size'])) {
                    return $perPageDefault == $item['size'];
                } elseif (is_numeric($item)) {
                    return $perPageDefault == $item;
                }

                return false;
            }
        );

        if (empty($exist)) {
            throw new \LogicException(
                sprintf('Default page size "%d" must present in size items array', $perPageDefault)
            );
        }

        $options = $config->offsetGetByPath(ToolbarExtension::OPTIONS_PATH, []);

        // get user specified require js modules from options
        if (isset($options[MetadataIterableObject::REQUIRED_MODULES_KEY])) {
            $data->offsetAddToArray(
                MetadataIterableObject::REQUIRED_MODULES_KEY,
                $options[MetadataIterableObject::REQUIRED_MODULES_KEY]
            );
            unset($options[MetadataIterableObject::REQUIRED_MODULES_KEY]);
        }

        // grid options passed under "options" node
        $data->offsetAddToArray(self::METADATA_KEY, $options);
    }
}
