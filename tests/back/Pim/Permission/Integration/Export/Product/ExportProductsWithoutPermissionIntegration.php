<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Export\Product;

use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                  Attribute groups                   |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+-----------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 */
class ExportProductsWithoutPermissionIntegration extends AbstractProductExportTestCase
{
    public function testProductViewableByRedactorWithoutPermissionApplied(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_not_viewable_by_redactor');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_2');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;PACK-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
{$product1->getUuid()->toString()};product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product2->getUuid()->toString()};product_viewable_by_everybody_1;categoryA2;1;;;files/product_viewable_by_everybody_1/a_localizable_image/de_DE/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/en_US/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;;;;;
{$product3->getUuid()->toString()};product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product4->getUuid()->toString()};product_without_category;;1;;;;;;;;;;;;;;;;;;;;;;product_not_viewable_by_redactor,product_viewable_by_everybody_2;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
            'with_uuid' => true,
        ];

        $csv = $this->jobLauncher->launchExport('csv_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductViewableByManagerWithoutPermissionApplied(): void
    {
        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_not_viewable_by_redactor');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_2');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;PACK-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
{$product1->getUuid()->toString()};product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product2->getUuid()->toString()};product_viewable_by_everybody_1;categoryA2;1;;;files/product_viewable_by_everybody_1/a_localizable_image/de_DE/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/en_US/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;;;;;
{$product3->getUuid()->toString()};product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product4->getUuid()->toString()};product_without_category;;1;;;;;;;;;;;;;;;;;;;;;;product_not_viewable_by_redactor,product_viewable_by_everybody_2;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
            'with_uuid' => true,
        ];

        $csv = $this->jobLauncher->launchExport('csv_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductViewableByRedactorWithQueueJobLauncher(): void
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . JobLauncher::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => 'csv_product_export']);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');

        $product1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_not_viewable_by_redactor');
        $product2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_1');
        $product3 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_viewable_by_everybody_2');
        $product4 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $expectedCsv = <<<CSV
uuid;sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;PACK-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
{$product1->getUuid()->toString()};product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product2->getUuid()->toString()};product_viewable_by_everybody_1;categoryA2;1;;;files/product_viewable_by_everybody_1/a_localizable_image/de_DE/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/en_US/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;;;;;
{$product3->getUuid()->toString()};product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
{$product4->getUuid()->toString()};product_without_category;;1;;;;;;;;;;;;;;;;;;;;;;product_not_viewable_by_redactor,product_viewable_by_everybody_2;

CSV;
        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
            'storage' => ['type' => 'local', 'file_path' => $filePath],
            'with_uuid' => true,
        ];

        $jobExecution = $this->get('akeneo_batch_queue.launcher.queue_job_launcher')->launch($jobInstance, $user, $config);
        $this->jobLauncher->launchConsumerOnce();
        $this->jobLauncher->waitCompleteJobExecution($jobExecution);

        $csv = file_get_contents($filePath);

        $this->assertSame($expectedCsv, $csv);
    }
}
