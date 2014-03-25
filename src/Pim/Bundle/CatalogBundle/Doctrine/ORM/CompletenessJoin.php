<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

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
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
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
     *
     * @return ComplenessJoin
     */
    public function addJoins($completenessAlias)
    {
        $rootAlias    = $this->qb->getRootAlias();
        $localeAlias  = $completenessAlias.'Locale';
        $channelAlias = $completenessAlias.'Channel';

        $this->qb
            ->leftJoin(
                'PimCatalogBundle:Locale',
                $localeAlias,
                'WITH',
                $localeAlias.'.code = :dataLocale'
            )
            ->leftJoin(
                'PimCatalogBundle:Channel',
                $channelAlias,
                'WITH',
                $channelAlias.'.code = :scopeCode'
            )
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Completeness',
                $completenessAlias,
                'WITH',
                $completenessAlias.'.locale = '.$localeAlias.'.id AND '.
                $completenessAlias.'.channel = '.$channelAlias.'.id AND '.
                $completenessAlias.'.product = '.$rootAlias.'.id'
            );

        return $this;
    }
}
