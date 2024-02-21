<?php

namespace Oro\Bundle\PimDataGridBundle\Datagrid\Configuration;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

/**
 * Configurator interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConfiguratorInterface
{
    /** @staticvar string */
    const SOURCE_PATH = '[source][%s]';

    /** @staticvar string */
    const SOURCE_KEY = 'source';

    /** @staticvar string */
    const AVAILABLE_COLUMNS_KEY = 'available_columns';

    /** @staticvar string */
    const DISPLAYED_ATTRIBUTES_KEY = 'displayed_attribute_ids';

    /** @staticvar string */
    const DISPLAYED_COLUMNS_KEY = 'displayed_columns';

    /** @staticvar string */
    const DISPLAYED_LOCALE_KEY = 'locale_code';

    /** @staticvar string */
    const DISPLAYED_SCOPE_KEY = 'scope_code';

    /** @staticvar string */
    const REPOSITORY_PARAMETERS_KEY = 'repository_parameters';

    /** @staticvar string */
    const USEABLE_ATTRIBUTES_KEY = 'attributes_configuration';

    /**
     * Configure the grid
     *
     * @param DatagridConfiguration $configuration
     */
    public function configure(DatagridConfiguration $configuration);
}
