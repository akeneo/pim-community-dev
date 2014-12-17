<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Join;

use Doctrine\ORM\QueryBuilder;

/**
 * Join utils class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessJoin
{
    /** @var QueryBuilder */
    protected $qb;

    /**
     * Instanciate the utility
     *
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * Add completeness joins to query builder
     *
     * @param string $completenessAlias the join alias
     * @param string $locale            the locale
     * @param sting  $scope             the scope
     *
     * @return CompletenessJoin
     */
    public function addJoins($completenessAlias, $locale, $scope)
    {
        $rootAlias         = $this->qb->getRootAlias();
        $localeAlias       = $completenessAlias.'Locale';
        $channelAlias      = $completenessAlias.'Channel';
        $rootEntity        = current($this->qb->getRootEntities());
        $completenessMapping = $this->qb->getEntityManager()
            ->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass = $completenessMapping['targetEntity'];

        $this->qb
            ->leftJoin(
                'PimCatalogBundle:Locale',
                $localeAlias,
                'WITH',
                $localeAlias.'.code = :cLocaleCode'
            )
            ->leftJoin(
                'PimCatalogBundle:Channel',
                $channelAlias,
                'WITH',
                $channelAlias.'.code = :cScopeCode'
            )
            ->leftJoin(
                $completenessClass,
                $completenessAlias,
                'WITH',
                $completenessAlias.'.locale = '.$localeAlias.'.id AND '.
                $completenessAlias.'.channel = '.$channelAlias.'.id AND '.
                $completenessAlias.'.product = '.$rootAlias.'.id'
            )
            ->setParameter('cLocaleCode', $locale)
            ->setParameter('cScopeCode', $scope);

        return $this;
    }
}
