<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Repository\Sql;

use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\CategorizedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\CreatedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event\UncategorizedProduct;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Product\Product;
use Akeneo\Pim\Enrichment\Bundle\MassiveImport\Repository\ProductRepository as ProductrepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ProductRepository
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository implements ProductRepositoryInterface
{
    public static $normalizeValues = 0;
    public static $persistValues = 0;
    public static $createProduct = 0;
    public static $total = 0;

    /** @var Connection */
    private $connection;

    /** @var NormalizerInterface */
    private $valuesNormalizer;

    /**
     * @param Connection          $connection
     * @param NormalizerInterface $valuesNormalizer
     */
    public function __construct(Connection $connection, NormalizerInterface $valuesNormalizer)
    {
        $this->connection = $connection;
        $this->valuesNormalizer = $valuesNormalizer;
    }

    public function persist(Product $product)
    {
        // should be the responsibility of the command bus
        $this->connection->beginTransaction();

        $this->createProduct($product);
        $this->persistCategories($product);
        $this->persistValues($product);

        $start = microtime(true);
        $this->connection->commit();
        $end = microtime(true) - $start;
        static::$total+= $end;
    }

    public function get(int $identifier): Product
    {
        throw new \InvalidArgumentException();
    }


    private function persistCategories(Product $product): void
    {
        if (empty($this->filterEvents($product->events(), CategorizedProduct::class))
        || empty($this->filterEvents($product->events(), UncategorizedProduct::class))) {
            return;
        };

        foreach ($product->categories() as $category) {
            $sql = <<<SQL
                INSERT INTO pim_catalog_category_product (product_id, category_id)
                SELECT p.id, c.id
                FROM pim_catalog_product p, pim_catalog_category c
                WHERE p.identifier = :identifier AND c.code = :category_code
SQL;

            $this->connection->executeQuery(
                $sql,
                [
                    'identifier'  => $product->identifier(),
                    'category_code' => $category
                ]
            );
        }

        $sql =<<<SQL
             DELETE FROM pim_catalog_category_product
             WHERE product_id =  (SELECT id from pim_catalog_product WHERE identifier = :identifier)
             AND category_id NOT IN (SELECT id FROM pim_catalog_category WHERE code IN (:category_codes));
SQL;

        $this->connection->executeQuery(
            $sql,
            [
                'identifier'  => $product->identifier(),
                'category_codes' => $product->categories()
            ],
            [
                'category_codes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            ]
        );
    }

    private function persistValues(Product $product): void
    {
        if ($product->valueCollection()->isEmpty()) {
            return;
        }

        $start = microtime(true);
        $rawValues = $this->valuesNormalizer->normalize($product->valueCollection(), 'storage');
        $end = microtime(true) - $start;
        static::$normalizeValues += $end;

        $sql = 'UPDATE pim_catalog_product p SET raw_values = :raw_values WHERE p.identifier = :identifier;';
        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue('identifier', $product->identifier());
        $stmt->bindValue('raw_values', $rawValues, Type::JSON_ARRAY);
        $start = microtime(true);
        $stmt->execute();
        $end = microtime(true) - $start;
        static::$persistValues += $end;
    }

    private function createProduct(Product $product): void
    {
        if (empty($this->filterEvents($product->events(), CreatedProduct::class))) {
            return;
        };

        $sql = 'INSERT INTO pim_catalog_product (identifier, is_enabled, raw_values, created, updated)
        values (:identifier, :enabled, :raw_values, :created, :updated)';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('identifier', $product->identifier());
        $stmt->bindValue('enabled', $product->enabled(), Type::BOOLEAN);
        $stmt->bindValue('raw_values', [], Type::JSON_ARRAY);
        $stmt->bindValue('created', $product->createdDate(), Type::DATETIME);
        $stmt->bindValue('updated', $product->updatedDate(), Type::DATETIME);
        $start = microtime(true);
        $stmt->execute();
        $end = microtime(true) - $start;
        static::$createProduct += $end;
    }

    private function filterEvents(array $events, string $eventClassName): array
    {
        return  array_filter($events, function($event) use ($eventClassName) {
            return get_class($event) == $eventClassName;
        });
    }
}
