<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessJoin;

/**
 * Completeness sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessSorter implements FieldSorterInterface
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * Instanciate a sorter
     *
     * @param QueryBuilder $qb
     * @param string       $locale
     * @param scope        $scope
     */
    public function __construct(QueryBuilder $qb, $locale, $scope)
    {
        $this->qb     = $qb;
        $this->locale = $locale;
        $this->scope  = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $alias = 'sorterCompleteness';
        $util = new CompletenessJoin($this->qb);
        $util->addJoins($alias);
        $this->qb->addOrderBy($alias.'.ratio', $direction);

        return $this;
    }
}
