<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Webmozart\Assert\Assert;

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
    public function updateFromProducts(array $products): void
    {
        $this->deletePreviousPrefixes($products);
        $this->insertNewPrefixes($products);
    }

    /**
     * @param ProductInterface[] $products
     */
    private function insertNewPrefixes(array $products): void
    {
        /** @var AttributeInterface[] $identifierAttributes */
        $identifierAttributes = [$this->attributeRepository->getIdentifier()];
        $newPrefixes = [];
        foreach ($products as $product) {
            // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
            if (get_class($product) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct') {
                continue;
            }

            foreach ($identifierAttributes as $identifierAttribute) {
                $identifier = $product->getValue($identifierAttribute->getCode())?->getData();
                if (null === $identifier) {
                    continue;
                }

                Assert::string($identifier);
                $productIdentifier = new ProductIdentifier($identifier);
                foreach ($productIdentifier->getPrefixes() as $prefix => $number) {
                    $newPrefixes[] = [
                        $product->getUuid()->toString(),
                        $identifierAttribute->getId(),
                        $prefix,
                        $number,
                    ];
                }
            }
        }

        if (\count($newPrefixes) === 0) {
            return;
        }

        $placeholders = implode(',', array_fill(0, \count($newPrefixes), '(UUID_TO_BIN(?),?,?,?)'));

        $insertSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_prefixes (`product_uuid`, `attribute_id`, `prefix`, `number`) VALUES ${placeholders}
SQL;

        $placeholderIndex = 1;
        $statement = $this->connection->prepare($insertSql);
        foreach ($newPrefixes as $newPrefix) {
            $statement->bindValue($placeholderIndex++, $newPrefix[0]);
            $statement->bindValue($placeholderIndex++, $newPrefix[1], ParameterType::INTEGER);
            $statement->bindValue($placeholderIndex++, $newPrefix[2]);
            $statement->bindValue($placeholderIndex++, $newPrefix[3], ParameterType::INTEGER);
        }

        $statement->executeQuery();
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
            fn (ProductInterface $product): string => $product->getUuid()->getBytes(),
            $products
        );

        $this->connection->executeQuery(
            $deleteSql,
            ['product_uuids' => $productUuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );
    }
}
