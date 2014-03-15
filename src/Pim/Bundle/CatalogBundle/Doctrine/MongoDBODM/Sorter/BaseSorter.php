<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\SorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * Base sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSorter implements SorterInterface
{
    /** @var string */
    const NORMALIZED_FIELD = 'normalizedData';

    /** @var QueryBuilder */
    protected $qb;

    /** @var string */
    protected $locale;

    /** @var string */
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
    public function add(AbstractAttribute $attribute, $direction)
    {
        $sortField = ProductQueryUtility::getNormalizedValueField($attribute, $this->locale, $this->scope);
        $this->qb->sort(self::NORMALIZED_FIELD.'.'.$sortField, $direction);

        return $this;
    }
}
