<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator as CommunityCompletenessGenerator;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\EnterpriseCompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Enterprise completeness generator
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CompletenessGenerator extends CommunityCompletenessGenerator implements EnterpriseCompletenessGeneratorInterface
{
    /** @var Connection */
    protected $connection;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /**
     * @param DocumentManager            $documentManager
     * @param string                     $productClass
     * @param ChannelRepositoryInterface $channelRepository
     * @param FamilyRepositoryInterface  $familyRepository
     * @param AssetRepositoryInterface   $assetRepository
     * @param EntityManagerInterface     $manager
     */
    public function __construct(
        DocumentManager $documentManager,
        $productClass,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        AssetRepositoryInterface $assetRepository,
        EntityManagerInterface $manager
    ) {
        parent::__construct($documentManager, $productClass, $channelRepository, $familyRepository);

        $this->assetRepository = $assetRepository;
        $this->connection      = $manager->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldsNames(array $channels, array $familyReqs)
    {
        $fields = [];
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $expectedCompleteness                                = $channel->getCode() . '-' . $locale->getCode();
                $fields[$expectedCompleteness]                       = [];
                $fields[$expectedCompleteness]['channel']            = $channel->getId();
                $fields[$expectedCompleteness]['locale']             = $locale->getId();
                $fields[$expectedCompleteness]['reqs']               = [];
                $fields[$expectedCompleteness]['reqs']['attributes'] = [];
                $fields[$expectedCompleteness]['reqs']['prices']     = [];
                $fields[$expectedCompleteness]['reqs']['assets']     = [];

                foreach ($familyReqs[$channel->getCode()] as $requirement) {
                    /** @var AttributeRequirementInterface $requirement */
                    $fieldName = $this->getNormalizedFieldName($requirement->getAttribute(), $channel, $locale);

                    if (AbstractAttributeType::BACKEND_TYPE_PRICE === $requirement->getAttribute()->getBackendType()) {
                        $fields[$expectedCompleteness]['reqs']['prices'][$fieldName] = [];
                        foreach ($channel->getCurrencies() as $currency) {
                            $fields[$expectedCompleteness]['reqs']['prices'][$fieldName][] = $currency->getCode();
                        }
                    } elseif ('pim_assets_collection' === $requirement->getAttribute()->getAttributeType()) {
                        $fields[$expectedCompleteness]['reqs']['assets'][] = $fieldName;
                    } else {
                        $fields[$expectedCompleteness]['reqs']['attributes'][] = $fieldName;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCount(array $normalizedReqs, array $missingComp)
    {
        $requiredCount = parent::getRequiredCount($normalizedReqs, $missingComp);
        $assetsReqs    = $normalizedReqs[$missingComp]['reqs']['assets'];

        return $requiredCount + count($assetsReqs);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMissingCount(array $normalizedReqs, $missingComp, array $normalizedData, array $dataFields)
    {
        $missingCount = parent::getMissingCount($normalizedReqs, $missingComp, $normalizedData, $dataFields);
        $assetsReqs   = $normalizedReqs[$missingComp]['reqs']['assets'];

        $localeId  = $normalizedReqs[$missingComp]['locale'];
        $channelId = $normalizedReqs[$missingComp]['channel'];

        $completeAttributes = 0;
        foreach ($assetsReqs as $attributeCode) {
            $completeAttributes += $this->countCompleteAssetsForAttribute($attributeCode, $localeId, $channelId);
        }

        $missingAssetsCount = count($assetsReqs) - $completeAttributes;

        return $missingCount + $missingAssetsCount;
    }

    /**
     * @param string $attributeCode
     * @param int    $localeId
     * @param int    $channelId
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    protected function countCompleteAssetsForAttribute($attributeCode, $localeId, $channelId)
    {
        $selectSql = 'SELECT av.value_id,
            IF (r.locale_id IS NOT NULL, r.locale_id, cl.locale_id) AS locale_id,
            v.channel_id

            FROM pim_catalog_product_value_asset av
            JOIN pim_catalog_product_value pv ON av.value_id = pv.id
            JOIN pim_catalog_attribute att ON att.id = pv.attribute_id AND att.code = :attributeCode
            JOIN pimee_product_asset_asset a ON av.asset_id = a.id
            JOIN pimee_product_asset_reference r ON r.asset_id = a.id
            JOIN pimee_product_asset_variation v ON v.reference_id = r.id
            LEFT JOIN pim_catalog_channel_locale AS cl ON v.channel_id = cl.channel_id AND r.locale_id IS NULL

            WHERE (r.locale_id = :localeId OR cl.locale_id = :localeId)
            AND v.channel_id = :channelId

            GROUP BY value_id, locale_id, channel_id

            HAVING COUNT(v.file_id) > 0';

        $stmt = $this->connection->prepare($selectSql);

        $stmt->bindValue(':attributeCode', $attributeCode);
        $stmt->bindValue(':localeId', $localeId);
        $stmt->bindValue(':channelId', $channelId);

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForAsset(AssetInterface $asset)
    {
        $productQb = $this->documentManager->createQueryBuilder($this->productClass);

        $productQb
            ->update()
            ->multiple(true)
            ->field('normalizedData.gallery.id')->equals($asset->getId())
            ->field('completenesses')->unsetField()
            ->field('normalizedData.completenesses')->unsetField()
            ->getQuery()
            ->execute();
    }
}
