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
class ExportProductsWithPermissionsIntegration extends AbstractProductExportTestCase
{
    public function testProductViewableByRedactor()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_number_float;PACK-groups;PACK-products;PACK-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
product_viewable_by_everybody_1;categoryA2;1;;;files/8df9e79b-f95e-44a5-8b56-d961f2b34f08/a_localizable_image/en_US/akeneo.jpg;files/8df9e79b-f95e-44a5-8b56-d961f2b34f08/a_localizable_image/fr_FR/akeneo.jpg;"EN tablet";"FR tablet";12.0500;;;;;;;;;;;;
product_viewable_by_everybody_2;categoryA2;1;;;;;;;;;;;;;;;;;;;
product_without_category;;1;;;;;;;;;;;;;;;;;;product_viewable_by_everybody_2;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_export', 'mary', $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductViewableByManager()
    {
        $uuid1 = $this->getProductUuid('product_not_viewable_by_redactor');
        $uuid2 = $this->getProductUuid('product_viewable_by_everybody_2');
        if ($uuid1->compareTo($uuid2) > 0) {
            $expectedOrder = 'product_viewable_by_everybody_2,product_not_viewable_by_redactor';
        } else {
            $expectedOrder = 'product_not_viewable_by_redactor,product_viewable_by_everybody_2';
        }

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;PACK-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
product_viewable_by_everybody_1;categoryA2;1;;;files/8df9e79b-f95e-44a5-8b56-d961f2b34f08/a_localizable_image/de_DE/akeneo.jpg;files/8df9e79b-f95e-44a5-8b56-d961f2b34f08/a_localizable_image/en_US/akeneo.jpg;files/8df9e79b-f95e-44a5-8b56-d961f2b34f08/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;;;;;
product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;;;;;
product_without_category;;1;;;;;;;;;;;;;;;;;;;;;;${expectedOrder};

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_export', 'julia', $config);
        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductExportWithNotGrantedPermissionsOnCategory()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups
product_viewable_by_everybody_2;categoryA2;1;;

CSV;

        $config = [
            'filters' => [
                'data'      => [
                    [
                        'field'    => 'categories',
                        'operator' => 'IN',
                        'value'    => ['categoryB'],
                    ],
                ],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_export', 'mary', $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductExportWithNotGrantedPermissionsOnAttributes()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;X_SELL-groups;X_SELL-products;X_SELL-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;PACK-groups;PACK-products;PACK-product_models
product_viewable_by_everybody_1;categoryA2;1;;;;;;;;;;;;;;
product_viewable_by_everybody_2;categoryA2;1;;;;;;;;;;;;;;
product_without_category;;1;;;;product_viewable_by_everybody_2;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                    'attributes' => ['a_metric_without_decimal_negative']
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_export', 'mary', $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductViewableByRedactorWithAuthenticatedJobLauncher()
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

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;X_SELL-groups;X_SELL-products;X_SELL-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;PACK-groups;PACK-products;PACK-product_models
product_viewable_by_everybody_1;categoryA2;1;;;;;;;;;;;;;;
product_viewable_by_everybody_2;categoryA2;1;;;;;;;;;;;;;;
product_without_category;;1;;;;product_viewable_by_everybody_2;;;;;;;;;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                    'attributes' => ['a_metric_without_decimal_negative']
                ],
            ],
            'storage' => ['type' => 'local', 'file_path' => $filePath],
        ];

        $jobExecution = $this->get('pim_connector.launcher.authenticated_job_launcher')->launch($jobInstance, $user, $config);
        $this->jobLauncher->launchConsumerOnce();
        $this->jobLauncher->waitCompleteJobExecution($jobExecution);

        $csv = file_get_contents($filePath);

        $this->assertSame($expectedCsv, $csv);
    }
}
