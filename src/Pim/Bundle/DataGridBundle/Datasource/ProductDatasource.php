<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;

/**
 * Product datasource, allows to prepare query builder from repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDatasource extends Datasource
{

    // TODO : PQB as instance variable !!!!
    // TODO : be sure to inject it in a dedicated datasource adapter, cf ./vendor/oro/platform/src/Oro/Bundle/FilterBundle/Grid/Extension/OrmFilterExtension.php:

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'locale_code'              => $this->getConfiguration('locale_code'),
            'scope_code'               => $this->getConfiguration('scope_code'),
            'attributes_configuration' => $this->getConfiguration('attributes_configuration'),
            'current_group_id'         => $this->getConfiguration('current_group_id', false),
            'association_type_id'      => $this->getConfiguration('association_type_id', false),
            'current_product'          => $this->getConfiguration('current_product', false)
        ];

        if (method_exists($this->qb, 'setParameters')) {
            QueryBuilderUtility::removeExtraParameters($this->qb);
        }

        $rows = $this->hydrator->hydrate($this->qb, $options);

        return $rows;
    }
}
