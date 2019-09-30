<?php

declare(strict_types=1);

namespace Akeneo\Bundle\RuleEngineBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ProductModelCompletenessWithRuleIntegration extends TestCase
{
    /**
     * Fix PIM-7336.
     */
    public function test_descendant_products_indexation_during_import_with_rules(): void
    {
        $product = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier(' 1111111113');
        $jobLauncher = new JobLauncher(static::$kernel);

        $query = <<<SQL
SELECT channel.code as channel_code, locale.code as locale_code, completeness.missing_count 
FROM pim_catalog_product product
JOIN pim_catalog_completeness completeness ON completeness.product_id = product.id
JOIN pim_catalog_locale locale ON locale.id = completeness.locale_id
JOIN pim_catalog_channel channel ON channel.id = completeness.channel_id
WHERE product.identifier = '1111111113' 
ORDER BY channel_code, locale_code;
SQL;

        $completenesses =  $this->get('database_connection')->fetchAll($query);

        Assert::assertSame([
            ['channel_code' => 'ecommerce', 'locale_code' => 'de_DE', 'missing_count' => '6'],
            ['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR', 'missing_count' => '6'],
            ['channel_code' => 'mobile', 'locale_code' => 'de_DE', 'missing_count' => '5'],
            ['channel_code' => 'mobile', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code' => 'mobile', 'locale_code' => 'fr_FR', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'de_DE', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'missing_count' => '5']
        ], $completenesses);


        $content = <<<TEXT
code;material
amor;cotton
TEXT;
        $jobLauncher->launchAuthenticatedImport('csv_product_model_import_with_rules', $content, 'admin');

        $completenesses =  $this->get('database_connection')->fetchAll($query, ['identifier' => '1111111113']);

        Assert:self::assertNotSame([
            ['channel_code' => 'ecommerce', 'locale_code' => 'de_DE', 'missing_count' => '6'],
            ['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR', 'missing_count' => '6'],
            ['channel_code' => 'mobile', 'locale_code' => 'de_DE', 'missing_count' => '5'],
            ['channel_code' => 'mobile', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code'     => 'mobile', 'locale_code' => 'fr_FR', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'de_DE', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'en_US', 'missing_count' => '5'],
            ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'missing_count' => '5']
        ], $completenesses);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
