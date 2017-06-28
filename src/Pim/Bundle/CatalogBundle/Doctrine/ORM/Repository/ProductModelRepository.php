<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelRepository extends EntityRepository implements ProductModelRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItemsFromIdentifiers(array $identifiers): array
    {
        $qb = $this
            ->createQueryBuilder('pm')
            ->where('pm.identifier IN (:identifiers)')
            ->setParameter('identifiers', $identifiers);

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['identifier'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?ProductModelInterface
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }
}
