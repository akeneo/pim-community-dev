<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Configure the displayed filters on the product datagrid based on user preferences
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureProductFiltersListener
{
    /** @var UserContext  */
    protected $context;

    /** @var array */
    protected $disallowed = ['scope', 'locale'];

    /**
     * @param UserContext $context
     */
    public function __construct(UserContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $defaults = $this->context->getUser()->getProductGridFilters();

        if (empty($defaults)) {
            return;
        }

        $configuration = $event->getDatagrid()->getAcceptor()->getConfig();

        foreach ($configuration['filters']['columns'] as $code => $filter) {
            if (in_array($code, $this->disallowed)) {
                continue;
            }

            $configuration->offsetSetByPath(
                sprintf('%s[%s][enabled]', Configuration::COLUMNS_PATH, $code),
                in_array($code, $defaults)
            );
        }
    }
}
