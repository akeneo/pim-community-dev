<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessRemover as BaseCompletenessRemover;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes as AssetAttributeTypes;
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Simple ORM version of the completeness remover.
 * Please note that completenesses are also removed from the index.
 *
 * @author    Julien Janvier (julien.janvier@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CompletenessRemover extends BaseCompletenessRemover implements CompletenessRemoverInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param EntityManagerInterface              $entityManager
     * @param ProductIndexer                      $indexer
     * @param string                              $completenessTable
     * @param AttributeRepositoryInterface        $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer,
        $completenessTable,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($pqbFactory, $entityManager, $indexer, $completenessTable);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function removeForAsset(AssetInterface $asset)
    {
        $attributesCodes = $this->attributeRepository->getAttributeCodesByType(AssetAttributeTypes::ASSETS_COLLECTION);

        foreach ($attributesCodes as $attributesCode) {
            $pqb = $this->createProductQueryBuilder();
            $pqb->addFilter($attributesCode, Operators::IN_LIST, [$asset->getCode()]);
            $products = $pqb->execute();

            $this->bulkRemoveCompletenesses($products);
        }
    }
}
