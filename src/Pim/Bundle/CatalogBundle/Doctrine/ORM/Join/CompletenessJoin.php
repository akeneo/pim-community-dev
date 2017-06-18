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
     * @param string $scope             the scope
     *
     * @return CompletenessJoin
     */
    public function addJoins($completenessAlias, $locale, $scope)
    {
        $rootAlias = current($this->qb->getRootAliases());
        $localeAlias = $completenessAlias.'Locale';
        $channelAlias = $completenessAlias.'Channel';

        $rootEntity = $this->qb->getRootEntities()[0];
        $completenessMapping = $this->qb->getEntityManager()
            ->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass = $completenessMapping['targetEntity'];

        $joinCondition = sprintf('%s.product = %s.id', $completenessAlias, $rootAlias);

        $this->qb
            ->leftJoin(
                'PimCatalogBundle:Channel',
                $channelAlias,
                'WITH',
                $channelAlias.'.code = :cScopeCode'
            )
            ->setParameter('cScopeCode', $scope);

        $joinCondition .= sprintf(' AND %s.channel = %s.id', $completenessAlias, $channelAlias);

        if (null !== $locale) {
            $this->qb
                ->leftJoin(
                    'PimCatalogBundle:Locale',
                    $localeAlias,
                    'WITH',
                    sprintf('%s.code = :%scLocaleCode', $localeAlias, $localeAlias)
                )
                ->setParameter($localeAlias.'cLocaleCode', $locale);

            $joinCondition .= sprintf(' AND %s.locale = %s.id', $completenessAlias, $localeAlias);
        }

        $this->qb
            ->leftJoin(
                $completenessClass,
                $completenessAlias,
                'WITH',
                $joinCondition
            );

        return $this;
    }
}
