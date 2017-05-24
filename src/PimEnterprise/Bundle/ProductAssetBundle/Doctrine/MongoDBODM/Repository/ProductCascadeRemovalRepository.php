<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductCascadeRemovalRepositoryInterface;

/**
 * Updates product document when an entity related to product is removed
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductCascadeRemovalRepository extends DocumentRepository implements ProductCascadeRemovalRepositoryInterface
{
    /** @var string */
    protected $documentName;

    /** @var DocumentManager */
    protected $dm;

    /**
     * @param DocumentManager $dm
     * @param string          $documentName
     */
    public function __construct(DocumentManager $dm, $documentName)
    {
        $this->dm = $dm;
        $this->documentName = $documentName;
    }

    /**
     * {@inheritdoc}
     */
    public function cascadeAssetRemoval(AssetInterface $asset, array $attributeCodes)
    {
        $qb = $this->dm->createQueryBuilder($this->documentName);
        $qb
            ->update()
            ->multiple(true)
            ->field('values.assetIds')->in([$asset->getId()])
            ->field('values.$.assetIds')->pull((int) $asset->getId())
            ->getQuery()
            ->execute();

        foreach ($attributeCodes as $attributeCode) {
            $this->removeNormalizedPart($asset, $attributeCode);
        }
    }

    /**
     * Remove normalizedPart corresponding to the asset in the attribute from a Product
     *
     * @param AssetInterface $asset
     * @param string         $attributeCode
     */
    protected function removeNormalizedPart(AssetInterface $asset, $attributeCode)
    {
        $qb = $this->dm->createQueryBuilder($this->documentName);

        $qb
            ->update()
            ->multiple(true)
            ->field(sprintf('normalizedData.%s', $attributeCode))
            ->pull(['id' => (int) $asset->getId()])
            ->getQuery()
            ->execute();
    }
}
