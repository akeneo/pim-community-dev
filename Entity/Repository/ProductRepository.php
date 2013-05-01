<?php

namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository
{
    /**
     * Build query builder
     *
     * @param integer $id     the id
     * @param string  $locale the locale
     *
     * @return QueryBuilder
     */
    public function buildOneLocalized($id, $locale)
    {
        $qb = $this->buildOne($id);

        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('Value.locale'),
                    $qb->expr()->eq('Value.locale', $qb->expr()->literal($locale))
                )
            )
            ->orderBy('Attribute.sortOrder');
    }

    /**
     * Add joins
     *
     * @return QueryBuilder
     */
    protected function build()
    {
        return parent::build()
            ->leftJoin($this->getAlias().'.values', 'Value')
            ->leftJoin('Value.attribute', 'Attribute')
            ->leftJoin('Value.options', 'ValueOption')
            ->leftJoin('ValueOption.optionValues', 'AttributeOptionValue');
    }
}
