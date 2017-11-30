<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetRepository implements IdentifiableObjectRepositoryInterface
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?AssetInterface
    {
        return $this->assetRepository->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }
}
