<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
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

    /** @var string */
    protected $completenessTable;

    /** @var CacheClearerInterface */
    protected $clearer;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param EntityManagerInterface              $entityManager
     * @param ProductIndexer                      $indexer
     * @param string                              $completenessTable
     * @param CacheClearerInterface               $clearer
     *
     * TODO: Pull-up day. Refactor before merge in master.
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManagerInterface $entityManager,
        ProductIndexer $indexer,
        $completenessTable,
        CacheClearerInterface $clearer = null
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->entityManager = $entityManager;
        $this->indexer = $indexer;
        $this->completenessTable = $completenessTable;
        $this->clearer = $clearer;
    }

    /**
     * {@inheritdoc}
     */
    public function removeForProduct(ProductInterface $product)
    {
        $statement = $this->entityManager->getConnection()->prepare(sprintf('
            DELETE c
            FROM %s c
            WHERE c.product_id = :productId
        ', $this->completenessTable));
        $statement->bindValue('productId', $product->getId());
        $statement->execute();

        $product->getCompletenesses()->clear();

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

        $this->bulkRemoveCompletenesses($products, $channel, $locale);
    }

    /**
     * Drops the current completenesses from the database and from the index for
     * a list of products and optionally for a channel and/or locale.
     *
     * @param CursorInterface       $products
     * @param null|ChannelInterface $channel
     * @param null|LocaleInterface  $locale
     */
    protected function bulkRemoveCompletenesses(
        CursorInterface $products,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $bulkedProducts = [];
        $productIds = [];
        $queryParams = [];
        $queryTypes = [Connection::PARAM_INT_ARRAY];
        $bulkCounter = 0;

        $query = sprintf('DELETE c FROM %s c WHERE c.product_id IN (?)', $this->completenessTable);
        if (null !== $channel) {
            $query .= ' AND c.channel_id = ?';
            $queryParams[] = $channel->getId();
            $queryTypes[] = \PDO::PARAM_INT;
        }
        if (null !== $locale) {
            $query .= ' AND c.locale_id = ?';
            $queryParams[] = $locale->getId();
            $queryTypes[] = \PDO::PARAM_INT;
        }

        foreach ($products as $product) {
            $bulkedProducts[] = $product;
            $productIds[] = $product->getId();

            $this->clearProductCompleteness($product, $channel, $locale);
            if (self::BULK_SIZE === $bulkCounter) {
                $this->entityManager->getConnection()->executeQuery(
                    $query,
                    array_merge([$productIds], $queryParams),
                    $queryTypes
                );
                $this->indexer->indexAll($bulkedProducts);
                if (null !== $this->clearer) {
                    $this->clearer->clear();
                }

                $bulkedProducts = [];
                $productIds = [];
                $bulkCounter = 0;
            } else {
                $bulkCounter++;
            }
        }

        if (!empty($productIds)) {
            $this->entityManager->getConnection()->executeQuery(
                $query,
                array_merge([$productIds], $queryParams),
                $queryTypes
            );
            $this->indexer->indexAll($bulkedProducts);
            if (null !== $this->clearer) {
                $this->clearer->clear();
            }
        }
    }

    /**
     * @param ProductInterface      $product
     * @param null|ChannelInterface $channel
     * @param null|LocaleInterface  $locale
     */
    protected function clearProductCompleteness(
        ProductInterface $product,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        if (null === $channel && null === $locale) {
            $product->getCompletenesses()->clear();
        } else {
            $productCompletenesses = $product->getCompletenesses();
            $completenessesToRemove = $productCompletenesses->filter(
                function (CompletenessInterface $completeness) use ($channel, $locale) {
                    return $channel === $completeness->getChannel() && $locale === $completeness->getLocale();
                }
            );

            foreach ($completenessesToRemove as $completeness) {
                $productCompletenesses->removeElement($completeness);
            }
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
