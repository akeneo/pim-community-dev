<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Pim\Component\Catalog\Repository\MassActionRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Repository dedicated to mass edit actions on assets.
 *
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class AssetMassEditRepository implements MassActionRepositoryInterface
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var BulkRemoverInterface */
    protected $assetRemover;

    /*
     * @param AssetRepositoryInterface $assetRepository
     * @param BulkRemoverInterface     $assetRemover
     */
    public function __construct(AssetRepositoryInterface $assetRepository, BulkRemoverInterface $assetRemover)
    {
        $this->assetRepository = $assetRepository;
        $this->assetRemover = $assetRemover;
    }

    /**
     * @param mixed $qb
     * @param bool  $inset
     * @param array $values
     *
     * @return mixed
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if (!empty($values)) {
            $rootAlias = $qb->getRootAlias();
            $valueWhereCondition = $inset ?
                $qb->expr()->in($rootAlias, $values) :
                $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        if (null !== $qb->getDQLPart('where')) {
            $whereParts = $qb->getDQLPart('where')->getParts();
            $qb->resetDQLPart('where');

            foreach ($whereParts as $part) {
                if (!is_string($part) || !strpos($part, 'entityIds')) {
                    $qb->andWhere($part);
                }
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        $qb->resetDQLPart('orderBy');
        $qb->setMaxResults(null);
    }

    /**
     * Deletes a list of assets.
     *
     * @param int[] $ids
     *
     * @return int Number of impacted rows
     */
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $assets = $this->assetRepository->findByIds($ids);
        $this->assetRemover->removeAll($assets);

        return count($ids);
    }
}
