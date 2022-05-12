<?php

namespace Akeneo\Channel\Infrastructure\Doctrine\Repository\InternalApi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * Channel repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelRepository extends EntityRepository implements DatagridRepositoryInterface
{
    public function __construct(EntityManager $em, string $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c');
        $rootAlias = $qb->getRootAliases()[0];

        $treeExpr = '(CASE WHEN ct.label IS NULL THEN category.code ELSE ct.label END)';

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $qb
            ->select($rootAlias)
            ->addSelect('category')
            ->addSelect(sprintf('%s AS categoryLabel', $treeExpr))
            ->addSelect(sprintf('%s AS channelLabel', $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->innerJoin(sprintf('%s.category', $rootAlias), 'category')
            ->leftJoin('category.translations', 'ct', 'WITH', 'ct.locale = :localeCode')
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        $qb->distinct(true);

        return $qb;
    }
}
