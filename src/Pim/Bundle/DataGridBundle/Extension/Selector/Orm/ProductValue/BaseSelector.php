<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm\ProductValue;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

/**
 * Base value selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSelector implements SelectorInterface
{
    /**
     * this selector can be applied by other selectors, we ensure that it can't be applied twice
     *
     * @var string
     */
    protected $applied = false;

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        if ($this->applied === false) {
            $rootAlias    = $datasource->getQueryBuilder()->getRootAlias();
            $path = sprintf('[source][%s]', ContextConfigurator::DISPLAYED_ATTRIBUTES_KEY);
            $attributeIds = $configuration->offsetGetByPath($path);

            $datasource->getQueryBuilder()
                ->leftJoin(
                    $rootAlias.'.values',
                    'values',
                    'WITH',
                    'values.attribute IN (:attributeIds) '
                    .'AND (values.locale = :dataLocale OR values.locale IS NULL) '
                    .'AND (values.scope = :scopeCode OR values.scope IS NULL)'
                )
                ->addSelect('values')
                ->leftJoin('values.attribute', 'attribute')
                ->addSelect('attribute')
                ->setParameter('attributeIds', $attributeIds);
        }
        $this->applied = true;
    }
}
