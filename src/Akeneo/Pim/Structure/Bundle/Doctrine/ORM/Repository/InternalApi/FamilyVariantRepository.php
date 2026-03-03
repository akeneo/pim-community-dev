<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantRepository implements DatagridRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private string $entityName;

    public function __construct(EntityManagerInterface $entityManager, string $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    public function createDatagridQueryBuilder(array $parameters = []): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder()->select('fv')->from($this->entityName, 'fv');
        $rootAlias = $qb->getRootAliases()[0];

        $qb
            ->select($rootAlias)
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->andWhere('fv.family = :family_id');

        return $qb;
    }
}
