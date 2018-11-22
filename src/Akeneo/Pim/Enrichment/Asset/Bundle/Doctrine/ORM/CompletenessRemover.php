<?php

namespace Akeneo\Pim\Enrichment\Asset\Bundle\Doctrine\ORM;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes as AssetAttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\CompletenessRemover as BaseCompletenessRemover;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Doctrine\ORM\EntityManagerInterface;

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
     * @param BulkObjectDetacherInterface         $bulkDetacher
     * @param AttributeRepositoryInterface        $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer,
        $completenessTable,
        BulkObjectDetacherInterface $bulkDetacher,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($pqbFactory, $entityManager, $indexer, $completenessTable, $bulkDetacher);
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
