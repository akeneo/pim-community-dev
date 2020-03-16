<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use PHPUnit\Framework\Assert;

class CleanCompletenessForNonExistingProductsCommandIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function countCompleteness(): int
    {
        $sql = "select count(*) as line_count from pim_catalog_completeness";
        $lines = $this->get('database_connection')
            ->executeQuery($sql)
            ->fetch();

        return $lines['line_count'] + 0;
    }

    private function getProductLinesNumber(int $productId): int
    {
        $sql = "select count(*) as line_count from pim_catalog_completeness where product_id = :product_id";
        $lines = $this->get('database_connection')
            ->executeQuery($sql, ['product_id' => $product_id])
            ->fetch();

        return $lines['line_count'] + 0;
    }

    private function deleteProduct(string $identifier): int
    {
        $sql = "delete from pim_product_catalog where identifier = :id";

        return $this->get('database_connection')
            ->executeUpdate($sql, ['id' => $identifier]);
    }

    private function createProduct(string $identifier, array $data): int
    {
        $product = $this->get('pim_catalog.builder.product')
            ->createProduct($identifier);
        $this->get('pim_catalog.updater.product')
            ->update($product, $data);
        $this->get('pim_catalog.saver.product')
            ->save($product);

        return $product->getId();
    }

    public function test_with_no_product()
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $initial_rows_nb = $this->countCompleteness();
        $commandLauncher->execute('pim:completeness:clean');
        Assert::assertTrue($initial_rows_nb == $this->countCompleteness());
    }

    public function test_with_products()
    {
        $id1 = $this->createProduct('AAA1', ['values' => [ "a_yes_no" => [['scope' => null, 'locale' => null, 'data' => true]]]]);
        $this->get('pim_catalog.completeness.product.compute_and_persist')
            ->fromProductIdentifier('AAA1');
        $commandLauncher = new CommandLauncher(static::$kernel);
        $commandLauncher->execute('pim:completeness:clean');
        Assert::assertEquals(1, $this->countCompleteness());
        $this->deleteProduct('AAA1');
        Assert::assertEquals(1, $this->countCompleteness());
        $commandLauncher->execute('pim:completeness:clean');
        Assert::assertEquals(0, $this->countCompleteness());
    }
}
