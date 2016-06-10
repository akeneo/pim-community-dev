<?php

namespace Pim\Bundle\ImportExportBundle\Doctrine\MongoDBODM\Repository;

use Pim\Bundle\ImportExportBundle\Doctrine\Commun\AbstractProductSearchableRepository;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Product searchable repository (use by the select2)
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSearchableRepository extends AbstractProductSearchableRepository
{
    /**
     * {@inheritdoc}
     */
    protected function buildQuery(
        ProductQueryBuilderInterface $productQueryBuilder,
        $search,
        array $options
    ) {
        $productQueryBuilder->addFilter($options['attribute']->getCode(), Operators::CONTAINS, $search);

        $queryBuilder = $productQueryBuilder->getQueryBuilder()
            ->limit($options['limit']);

        if (1 !== (int)$options['page']) {
            $queryBuilder->skip($options['page'] * $options['limit']);
        }

        return $queryBuilder->getQuery();
    }
}
