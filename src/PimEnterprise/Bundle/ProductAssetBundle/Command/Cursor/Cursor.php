<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command\Cursor;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor as BaseCursor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Class Cursor to iterate assets from QueryBuilder.
 * This class has been done to iterate over entities that are not Cursorable.
 * And avoid BC by not adding the Cursorable Interface in the Repository.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Cursor extends BaseCursor
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /**
     * @param QueryBuilder  $queryBuilder
     * @param EntityManager $entityManager
     * @param int           $pageSize
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        EntityManager $entityManager,
        $pageSize
    ) {
        parent::__construct($queryBuilder, $entityManager, $pageSize);
    }

    /**
     * @throws LogicException
     *
     * @return AssetRepositoryInterface
     */
    protected function getRepository()
    {
        if (null === $this->repository) {
            $entityClass = current($this->queryBuilder->getDQLPart('from'))->getFrom();
            $this->repository = $this->entityManager->getRepository($entityClass);
        }

        return $this->repository;
    }
}
