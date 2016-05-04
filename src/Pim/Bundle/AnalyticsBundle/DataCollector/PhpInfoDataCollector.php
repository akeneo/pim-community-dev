<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;

/**
 * Returns some parts of phpinfo
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PhpInfoDataCollector implements DataCollectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return ['php_extensions' => get_loaded_extensions()];
    }
}
