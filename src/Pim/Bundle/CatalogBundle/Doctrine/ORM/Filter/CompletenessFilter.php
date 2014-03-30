<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessJoin;
use Doctrine\ORM\QueryBuilder;

/**
 * Completeness filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter implements FieldFilterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Instanciate a sorter
     *
     * @param QueryBuilder   $qb
     * @param CatalogContext $context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $alias = 'filterCompleteness';
        $field = $alias.'.ratio';
        $util = new CompletenessJoin($this->qb);
        $util->addJoins($alias);

        if ($operator === '=') {
            $this->qb->andWhere($this->qb->expr()->eq($field, '100'));
        } else {
            $this->qb->andWhere($this->qb->expr()->lt($field, '100'));
        }

        return $this;
    }
}
