<?php

namespace AkeneoEnterprise\Test\Acceptance\ProductAsset\Asset;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

class InMemoryAssetRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, AssetRepositoryInterface
{
    /** @var ArrayCollection */
    private $assets;

    public function __construct(array $assets = [])
    {
        $this->assets = new ArrayCollection($assets);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->assets->get($identifier);
    }

    public function save($asset, array $options = [])
    {
        if (!$asset instanceof AssetInterface) {
            throw new \InvalidArgumentException('Object can only be an asset');
        }

        $this->assets->set($asset->getCode(), $asset);
    }

    public function findOneByCode($assetCode)
    {
        return $this->assets->get($assetCode);
    }

    public function createAssetDatagridQueryBuilder(array $parameters = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function applyTagFilter(QueryBuilder $qb, $field, $operator, $value)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function applyCategoriesFilter(QueryBuilder $qb, $operator, array $categoryCodes)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findByIdentifiers(array $identifiers = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findSimilarCodes($code)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function countCompleteAssets(array $assetIds, $localeId, $channelId)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findExpiringAssets(\DateTime $now, $delay = 5)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findByIds(array $assetIds)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findBySearch($search = null, array $options = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findCodesByIdentifiers(array $referenceDataCodes)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
