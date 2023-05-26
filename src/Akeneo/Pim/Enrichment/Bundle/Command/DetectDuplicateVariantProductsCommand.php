<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DetectDuplicateVariantProductsCommand extends Command
{
    protected static $defaultName = 'pim:product:detect-duplicate-variants';

    public function __construct(private readonly Connection $connection, private readonly WriteValueCollectionFactory $valueCollectionFactory)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $variantAxesByFamilyVariant = $this->getFamilyVariantAxes();

        foreach ($variantAxesByFamilyVariant as $familyVariantCode => $axes) {
            $productCount = 0;
            $foundDuplicates = false;
            $output->writeln(\sprintf('Searching duplicate variant products for the <info>%s</info> family variant...', $familyVariantCode));

            foreach ($this->getProductModels($familyVariantCode) as $parentCode) {
                $variantProducts = $this->getVariantProductsByParent($parentCode, $axes);
                $productCount += count($variantProducts);
                $foundDuplicates = $this->searchDuplicates($output, $parentCode, $variantProducts, $axes) && $foundDuplicates;
            }

            if (!$foundDuplicates) {
                $output->writeln('<info>OK</info>');
            }

            $output->writeln($productCount . ' variant products analyzed');
        }

        return self::SUCCESS;
    }

    private function getVariantProductsByParent(string $parentCode, array $axes): array
    {
        $searchAfter = 0;

        $axesValues = \sprintf(
            'JSON_OBJECT(%s) AS axes_values',
            \implode(', ',
                \array_map(
                    static fn (string $attributeCode): string => \sprintf('\'%s\', JSON_EXTRACT(product.raw_values, \'$.%s\')', $attributeCode, $attributeCode),
                    $axes
                )
            )
        );

        $sql = \sprintf(
            <<<SQL
            SELECT BIN_TO_UUID(product.uuid) AS uuid, product.id as product_id, %s
            FROM pim_catalog_product product
            INNER JOIN pim_catalog_product_model product_model ON product.product_model_id = product_model.id
            AND product_model.code = :parentCode
            AND product.id > :searchAfter
            ORDER BY product.id ASC
            LIMIT 100;
            SQL,
            $axesValues
        );

        $productsByParent = [];

        do {
            $rows = $this->connection->fetchAllAssociative(
                $sql,
                [
                    'parentCode' => $parentCode,
                    'searchAfter' => $searchAfter,
                ]
            );

            foreach ($rows as $row) {
                $productsByParent[$row['uuid']] = \json_decode($row['axes_values'], true);
                $searchAfter = (int) $row['product_id'];
            }
        } while (count($rows) > 0);

        return $productsByParent;
    }

    private function getProductModels(string $familyVariantCode): iterable
    {
        $searchAfter = 0;
        $sql = <<<SQL
            SELECT model.code
            FROM pim_catalog_product_model model
            INNER JOIN pim_catalog_family_variant on model.family_variant_id = pim_catalog_family_variant.id
            WHERE pim_catalog_family_variant.code = :code            
            AND model.code > :searchAfter
            ORDER BY model.code
            LIMIT 100;
            SQL;
        do {
            $rows = $this->connection->fetchFirstColumn(
                $sql,
                [
                    'code' => $familyVariantCode,
                    'searchAfter' => $searchAfter,
                ],
            );
            foreach ($rows as $productModelCode) {
                $searchAfter = $productModelCode;
                yield $productModelCode;
            }
        } while (count($rows) > 0);
    }

    private function getFamilyVariantAxes(): array
    {
        $variantAxes = [];

        $rows = $this->connection->executeQuery(
            <<<SQL
                SELECT family_variant.code AS family_variant_code, level, JSON_ARRAYAGG(attribute.code) as axes
                FROM pim_catalog_family_variant family_variant
                INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets pcfvhvas on family_variant.id = pcfvhvas.family_variant_id
                INNER JOIN pim_catalog_family_variant_attribute_set attribute_set ON pcfvhvas.variant_attribute_sets_id = attribute_set.id
                INNER JOIN pim_catalog_variant_attribute_set_has_axes pcvasha on attribute_set.id = pcvasha.variant_attribute_set_id
                INNER JOIN pim_catalog_attribute attribute on pcvasha.axes_id = attribute.id
                GROUP BY family_variant.code, level
                ORDER BY family_variant.code ASC, level ASC
                SQL
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $variantAxes[$row['family_variant_code']] = \json_decode($row['axes'], true);
        }

        return $variantAxes;
    }

    private function searchDuplicates(OutputInterface $output, string $parentCode, array $productsGroupedByParent, array $axes): bool
    {
        $data = \array_map(
            fn (WriteValueCollection $values): string => $this->getCombinationSet($axes, $values),
            $this->valueCollectionFactory->createMultipleFromStorageFormat($productsGroupedByParent)
        );

        $duplicateValues = \array_filter(
            \array_count_values($data),
            static fn (int $count): bool => $count >= 2
        );

        if (0 === \count($duplicateValues)) {
            return false;
        }

        $table = new Table($output);
        $table->setStyle('box');
        $table->setHeaders(['parent', 'uuid', 'axes values']);
        foreach ($duplicateValues as $duplicateCombination => $count) {
            foreach ($data as $uuid => $combination) {
                if ($duplicateCombination === $combination) {
                    $table->addRow([$parentCode, $uuid, $combination]);
                }
            }
            $table->addRow(new TableSeparator());
        }
        $table->render();

        return true;
    }

    private function getCombinationSet(array $axes, WriteValueCollection $values): string
    {
        $combinationSet = \array_map(
            static fn (string $attributeCode): string => \mb_strtolower((string)$values->getByCodes($attributeCode)),
            $axes
        );

        return \implode(',', $combinationSet);
    }
}
