<?php

namespace Pim\Bundle\ImportExportBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\Query as ORMQuery;
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
class ProductSearchableRepository  extends AbstractProductSearchableRepository
{
    protected function buildQuery(
        ProductQueryBuilderInterface $productQueryBuilder,
        $search,
        array $options
    ) {
        $productQueryBuilder = $this->productQueryBuilderFactory->create();
        $productQueryBuilder->addFilter($options['attribute']->getCode(), Operators::CONTAINS, $search);

        $queryBuilder = $productQueryBuilder->getQueryBuilder()
            ->setMaxResults($options['limit']);
        
        if (1 !== (int)$options['page']) {
            $queryBuilder->setFirstResult($options['page'] * $options['limit']);
        }

        return $queryBuilder->getQuery();
    }
}
