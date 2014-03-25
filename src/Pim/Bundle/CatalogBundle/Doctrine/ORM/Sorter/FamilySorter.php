<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;

/**
 * Family sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySorter implements FieldSorterInterface
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
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

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
        $rootAlias = $this->qb->getRootAlias();

        $prefix    = 'sorter';
        $field     = $prefix.'familyLabel';
        $family    = $prefix.'family';
        $trans     = $prefix.'familyTranslations';

        $this->qb
            ->leftJoin($rootAlias.'.family', $family)
            ->leftJoin($family.'.translations', $trans, 'WITH', $trans.'.locale = :dataLocale');
        $this->qb
            ->addSelect('COALESCE('.$trans.'.label, CONCAT(\'[\', '.$family.'.code, \']\')) as '.$field);

        $this->qb->addOrderBy($field, $direction);

        return $this;
    }
}
