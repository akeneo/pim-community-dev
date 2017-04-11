<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductIndexer;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Simple ORM version of the completeness remover.
 * Please note that completenesses are also removed from the index.
 *
 * @author    Julien Janvier (julien.janvier@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessRemover implements CompletenessRemoverInterface
{
    const BULK_SIZE = 100;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var ProductIndexer */
    protected $indexer;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param EntityManagerInterface              $entityManager
     * @param ProductIndexer                      $indexer
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->entityManager = $entityManager;
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    public function removeForProduct(ProductInterface $product)
    {
        $product->getCompletenesses()->clear();
        $statement = $this->entityManager->getConnection()->executeQuery(
            'DELETE c FROM pim_catalog_completeness c JOIN pim_catalog_product p WHERE p.identifier = ?',
            [$product->getIdentifier()]
        );
        $statement->execute();

        $this->indexer->index($product);
    }

    /**
     * {@inheritdoc}
     */
    public function removeForFamily(FamilyInterface $family)
    {
        $familyFilter = ['field' => 'family', 'operator' => Operators::IN_LIST, 'value' => [$family->getCode()]];
        $products = $this->createProductQueryBuilder(null, null, [$familyFilter])->execute();

        $this->bulkRemoveCompletenesses($products);
    }

    /**
     * {@inheritdoc}
     */
    public function removeForChannelAndLocale(ChannelInterface $channel, LocaleInterface $locale)
    {
        $products = $this->createProductQueryBuilder($channel, $locale)->execute();

        $this->bulkRemoveCompletenesses($products);
    }

    /**
     * Drops the current completenesses from the database and from the index for a list of products.
     *
     * @param CursorInterface $products
     */
    protected function bulkRemoveCompletenesses(CursorInterface $products)
    {
        $statement = $this->entityManager->getConnection()->prepare('
            DELETE c
            FROM pim_catalog_completeness c
            JOIN pim_catalog_product p
            WHERE p.identifier IN (:identifiers)
        ');

        $bulkedProducts = [];
        $identifiers = [];
        $bulkCounter = 0;

        foreach ($products as $product) {
            $bulkedProducts[] = $product;
            $identifiers[] = $product->getIdentifier();

            if (self::BULK_SIZE === $bulkCounter) {
                $statement->bindValue('identifiers', $identifiers, Type::SIMPLE_ARRAY);
                $statement->execute();
                $this->indexer->indexAll($bulkedProducts);

                $bulkedProducts = [];
                $identifiers = [];
                $bulkCounter = 0;
            } else {
                $bulkCounter++;
            }

            $product->getCompletenesses()->clear();
        }

        if (!empty($identifiers)) {
            $statement->bindValue('identifiers', $identifiers, Type::SIMPLE_ARRAY);
            $statement->execute();
            $this->indexer->indexAll($bulkedProducts);
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param array            $filters
     *
     * @return ProductQueryBuilderInterface
     */
    protected function createProductQueryBuilder(
        ChannelInterface $channel = null,
        LocaleInterface $locale = null,
        array $filters = []
    ) {
        $options = [
            'filters' => $filters
        ];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }
        if (null !== $locale) {
            $options['default_locale'] = $locale->getCode();
        }

        return $this->pqbFactory->create($options);
    }
}
