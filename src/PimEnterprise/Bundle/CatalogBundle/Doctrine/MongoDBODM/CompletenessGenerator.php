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

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator as CommunityCompletenessGenerator;
use Pim\Component\Catalog\AttributeTypes as AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes as AssetAttributeTypes;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Enterprise completeness generator
 * Override of base generator to integrate assets in the completeness process
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CompletenessGenerator extends CommunityCompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @var Connection */
    protected $connection;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /**
     * @param DocumentManager                          $documentManager
     * @param ChannelRepositoryInterface               $channelRepository
     * @param FamilyRepositoryInterface                $familyRepository
     * @param AssetRepositoryInterface                 $assetRepository
     * @param AttributeRepositoryInterface             $attributeRepository
     * @param EntityManagerInterface                   $manager
     * @param string                                   $productClass
     * @param ProductQueryBuilderFactoryInterface|null $productQueryBuilderFactory
     */
    public function __construct(
        DocumentManager $documentManager,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        AssetRepositoryInterface $assetRepository,
        AttributeRepositoryInterface $attributeRepository,
        EntityManagerInterface $manager,
        $productClass,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory = null
    ) {
        parent::__construct($documentManager, $productClass, $channelRepository, $familyRepository);

        $this->assetRepository = $assetRepository;
        $this->attributeRepository = $attributeRepository;
        $this->connection = $manager->getConnection();
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function scheduleForAsset(AssetInterface $asset)
    {
        $attributesCodes = $this->getAssetCollectionAttributeCodes();
        foreach ($attributesCodes as $attributeCode) {
            $productsToReset = $this->getProductsWithAsset($asset, $attributeCode);
            if ($productsToReset->count() > 0) {
                $this->bulkResetCompleteness($productsToReset);
            }
        }
    }

    /**
     * @param ChannelInterface[] $channels
     * @param array              $familyReqs
     *
     * @return array
     */
    protected function getFieldsNames(array $channels, array $familyReqs)
    {
        $fields = [];
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $fields = $this->getFieldsNamesForChannelAndLocale($fields, $channel, $locale, $familyReqs);
            }
        }

        return $fields;
    }

    /**
     * @param array            $fields
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param array            $familyReqs
     *
     * @return array
     */
    protected function getFieldsNamesForChannelAndLocale(
        array $fields,
        ChannelInterface $channel,
        LocaleInterface $locale,
        array $familyReqs
    ) {
        $expectedCompleteness = $channel->getCode() . '-' . $locale->getCode();
        $fields[$expectedCompleteness] = [];
        $fields[$expectedCompleteness]['channel'] = $channel->getId();
        $fields[$expectedCompleteness]['locale'] = $locale->getId();
        $fields[$expectedCompleteness]['reqs'] = [];
        $fields[$expectedCompleteness]['reqs']['attributes'] = [];
        $fields[$expectedCompleteness]['reqs']['prices'] = [];
        $fields[$expectedCompleteness]['reqs']['assets'] = [];

        foreach ($familyReqs[$channel->getCode()] as $requirement) {
            $fieldName = $this->getNormalizedFieldName($requirement->getAttribute(), $channel, $locale);

            $attribute = $requirement->getAttribute();
            $shouldExistInLocale = !$attribute->isLocaleSpecific() || $attribute->hasLocaleSpecific($locale);

            if ($shouldExistInLocale) {
                if (AttributeTypes::BACKEND_TYPE_PRICE === $requirement->getAttribute()->getBackendType()) {
                    $fields[$expectedCompleteness]['reqs']['prices'][$fieldName] = [];
                    foreach ($channel->getCurrencies() as $currency) {
                        $fields[$expectedCompleteness]['reqs']['prices'][$fieldName][] = $currency->getCode();
                    }
                } elseif (AssetAttributeTypes::ASSETS_COLLECTION === $requirement->getAttribute()->getType()) {
                    $fields[$expectedCompleteness]['reqs']['assets'][] = $fieldName;
                } else {
                    $fields[$expectedCompleteness]['reqs']['attributes'][] = $fieldName;
                }
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredCount(array $normalizedReqs, $missingComp)
    {
        $requiredCount = parent::getRequiredCount($normalizedReqs, $missingComp);
        $assetsReqs = $normalizedReqs[$missingComp]['reqs']['assets'];

        return $requiredCount + count($assetsReqs);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMissingCount(array $normalizedReqs, array $normalizedData, array $dataFields, $missingComp)
    {
        $missingCount = parent::getMissingCount($normalizedReqs, $normalizedData, $dataFields, $missingComp);
        $assetsReqs = $normalizedReqs[$missingComp]['reqs']['assets'];

        $localeId = $normalizedReqs[$missingComp]['locale'];
        $channelId = $normalizedReqs[$missingComp]['channel'];

        $completeAttributes = 0;
        foreach ($assetsReqs as $attributeCode) {
            if (isset($normalizedData[$attributeCode])) {
                $assetIds = $this->getAssetsIdsFromAttribute($normalizedData, $attributeCode);
                $completeAssets = $this->assetRepository->countCompleteAssets($assetIds, $localeId, $channelId);
                if ($completeAssets > 0) {
                    $completeAttributes += 1;
                }
            }
        }

        $missingAssetsCount = count($assetsReqs) - $completeAttributes;

        return $missingCount + $missingAssetsCount;
    }

    /**
     * @param array  $normalizedData
     * @param string $attributeCode
     *
     * @return array
     */
    protected function getAssetsIdsFromAttribute(array $normalizedData, $attributeCode)
    {
        $assetsIds = [];

        foreach ($normalizedData[$attributeCode] as $assetValues) {
            $assetsIds[] = $assetValues['id'];
        }

        return $assetsIds;
    }

    /**
     * @return string[]
     */
    private function getAssetCollectionAttributeCodes()
    {
        return $this->attributeRepository->getAttributeCodesByType(AssetAttributeTypes::ASSETS_COLLECTION);
    }

    /**
     * @param AssetInterface $asset
     * @param string         $attributeCode
     *
     * @return CursorInterface
     */
    private function getProductsWithAsset(AssetInterface $asset, $attributeCode)
    {
        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter($attributeCode, Operators::IN_LIST, [$asset->getCode()]);

        return $pqb->execute();
    }

    /**
     * Resets the completeness of all the products passed in parameters.
     *
     * @param CursorInterface $products
     */
    private function bulkResetCompleteness(CursorInterface $products)
    {
        $productToResetIds = [];

        foreach ($products as $product) {
            $productToResetIds[] = $product->getId();

            if (0 === \count($productToResetIds) % 50) {
                $this->resetCompleteness($productToResetIds);
                $productToResetIds = [];
            }
        }

        if (!empty($productToResetIds)) {
            $this->resetCompleteness($productToResetIds);
        }
    }

    /**
     * Reset the completeness of the products corresponding to the ids passed in parameter
     *
     * @param array $productToResetIds
     */
    private function resetCompleteness(array $productToResetIds)
    {
        $productQb = $this->documentManager->createQueryBuilder($this->productClass);
        $productQb
            ->update()
            ->multiple(true);

        foreach ($productToResetIds as $id) {
            $productQb->addOr(
                $productQb->expr()->field('id')->equals($id)
            );
        }

        $productQb
            ->field('completenesses')->unsetField()
            ->field('normalizedData.completenesses')->unsetField()
            ->getQuery()
            ->execute();
    }
}
