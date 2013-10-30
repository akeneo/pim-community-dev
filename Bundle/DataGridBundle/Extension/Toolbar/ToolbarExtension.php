<?php

namespace Oro\Bundle\DataGridBundle\Extension\Toolbar;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ToolbarExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const METADATA_KEY = 'options';

    const OPTIONS_KEY                        = '[options]';
    const TOOLBAR_OPTION_PATH                = '[options][toolbarOptions]';
    const PAGER_ITEMS_OPTION_PATH            = '[options][toolbarOptions][pageSize][items]';
    const PAGER_DEFAULT_PER_PAGE_OPTION_PATH = '[options][toolbarOptions][pageSize][default_per_page]';

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $options = $config->offsetGetByPath(self::TOOLBAR_OPTION_PATH, []);
        // validate configuration and pass default values back to config
        $configuration = $this->validateConfiguration(new Configuration(), ['toolbarOptions' => $options]);
        $config->offsetAddToArrayByPath(self::OPTIONS_KEY, ['toolbarOptions' => $configuration]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        /**
         * Default toolbar options
         *  [
         *      'hide'       => false,
         *      'pageSize'   => [
         *          'hide'  => false,
         *          'items' => [10, 25, 50, 100]
         *       ],
         *      'pagination' => [
         *          'hide' => false,
         *      ]
         *  ];
         */

        $perPageDefault = $config->offsetGetByPath(self::PAGER_DEFAULT_PER_PAGE_OPTION_PATH);
        $pageSizeItems  = $config->offsetGetByPath(self::PAGER_ITEMS_OPTION_PATH);

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

        // grid options passed under "options" node
        $data->offsetAddToArray('options', $config->offsetGetOr('options', []));
    }
}
