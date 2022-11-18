<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class SqlUpdateIdentifierPrefixesQuery
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private Connection $connection,
    ) {
    }

    /**
     * @param ProductInterface[] $products
     */
    public function updateFromProducts(array $products)
    {
        $this->deletePreviousPrefixes($products);
        $this->insertNewPrefixes($products);
    }

    private function insertNewPrefixes(array $products): void
    {
        /** @var AttributeInterface[] $identifierAttributes */
        $identifierAttributes = [$this->attributeRepository->getIdentifier()];
        $newPrefixes = [];
        foreach ($products as $product) {
            foreach ($identifierAttributes as $identifierAttribute) {
                $newPrefixes[] = $this->getPrefixesAndNumbers(
                    $product->getValue($identifierAttribute->getCode())?->getData(),
                    $identifierAttribute->getId(),
                    $product->getUuid()->toString()
                );
            }
        }

        $flatNewPrefixes = \array_merge_recursive($newPrefixes);

        if (\count($flatNewPrefixes) === 0) {
            return;
        }

        $values = [];
        foreach ($flatNewPrefixes as $newPrefix) {
            $values[] = \sprintf('(UUID_TO_BIN("%s"), %d, "%s", %d)', ...$newPrefix);
        };

        $valuesStr = \implode(',', $values);

        $insertSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_prefixes (`product_uuid`, `attribute_id`, `prefix`, `number`) VALUES ${valuesStr}
SQL;

        $this->connection->executeQuery($insertSql);
    }

    /**
     * Returns the prefix and their associated number
     * Ex: "AKN-2012" will return ["AKN-" => 2012, "AKN-2" => 12, "AKN-20" => 12, "AKN-201" => 2]
     */
    private function getPrefixesAndNumbers(?string $identifier, int $attributeId, string $productUuid)
    {
        if (null === $identifier) {
            return [];
        }
        $results = [];
        for ($i = 0; $i < strlen($identifier); $i++) {
            $charAtI = substr($identifier, $i, 1);
            if (is_numeric($charAtI)) {
                $prefix = substr($identifier, 0, $i);
                $results[] = [$productUuid, $attributeId, $prefix, $this->getAllBeginningNumbers(substr($identifier, $i))];
            }
        }
        return $results;
    }

    /**
     * Returns all the beginning numbers from a string
     * Ex: "251-toto" will return 251
     */
    private function getAllBeginningNumbers(string $identifierFromAnInteger)
    {
        $result = '';
        $i = 0;
        while (is_numeric(substr($identifierFromAnInteger, $i, 1))) {
            $result = $result . substr($identifierFromAnInteger, $i, 1);
            $i++;
        }
        return \intval($result);
    }

    /**
     * @param ProductInterface[] $products
     */
    private function deletePreviousPrefixes(array $products): void
    {
        $deleteSql = <<<SQL
DELETE FROM pim_catalog_identifier_generator_prefixes WHERE product_uuid IN (:product_uuids)
SQL;

        $productUuidsAsBytes = \array_map(
            fn (ProductInterface $product): string => Uuid::fromString($product->getUuid())->getBytes(),
            $products
        );

        $this->connection->executeQuery($deleteSql,
            ['product_uuids' => $productUuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );
    }
}
