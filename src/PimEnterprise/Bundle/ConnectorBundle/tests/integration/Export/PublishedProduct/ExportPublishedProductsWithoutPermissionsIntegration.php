<?php

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Export\PublishedProduct;

use Akeneo\Test\Integration\JobLauncher;

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
class ExportPublishedProductsWithoutPermissionsIntegration extends AbstractPublishedProductExportTestCase
{
    public function testPublishedProductViewableByRedactorWithoutPermissionApplied()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;SUBSTITUTION-groups;SUBSTITUTION-products;UPSELL-groups;UPSELL-products;X_SELL-groups;X_SELL-products
product_viewable_by_everybody_1;categoryA2;1;;;files/product_viewable_by_everybody_1/a_localizable_image/de_DE/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/en_US/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;
product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;
product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;
product_without_category;;1;;;;;;;;;;;;;;;;;;;product_viewable_by_everybody_2,product_not_viewable_by_redactor

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
        $csv = $this->jobLauncher->launchExport('csv_published_product_export', null, $config);

        $this->assertSame($expectedCsv, $csv);
    }

    public function testPublishedProductViewableByRedactorWithQueueJobLauncher()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. JobLauncher::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => 'csv_published_product_export']);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');

        $expectedCsv = <<<CSV
sku;categories;enabled;family;groups;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_metric_without_decimal_negative;a_metric_without_decimal_negative-unit;a_number_float;PACK-groups;PACK-products;SUBSTITUTION-groups;SUBSTITUTION-products;UPSELL-groups;UPSELL-products;X_SELL-groups;X_SELL-products
product_viewable_by_everybody_1;categoryA2;1;;;files/product_viewable_by_everybody_1/a_localizable_image/de_DE/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/en_US/akeneo.jpg;files/product_viewable_by_everybody_1/a_localizable_image/fr_FR/akeneo.jpg;"DE tablet";"EN tablet";"FR tablet";-10;CELSIUS;12.0500;;;;;;;;
product_viewable_by_everybody_2;categoryA2,categoryB;1;;;;;;;;;;;;;;;;;;;
product_not_viewable_by_redactor;categoryB;1;;;;;;;;;;;;;;;;;;;
product_without_category;;1;;;;;;;;;;;;;;;;;;;product_viewable_by_everybody_2,product_not_viewable_by_redactor

CSV;
        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'tablet',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
            'filePath' => $filePath,
        ];

        $jobExecution = $this->get('akeneo_batch_queue.launcher.queue_job_launcher')->launch($jobInstance, $user, $config);
        $this->jobLauncher->launchConsumerOnce();
        $this->jobLauncher->waitCompleteJobExecution($jobExecution);

        $csv = file_get_contents($filePath);

        $this->assertSame($expectedCsv, $csv);
    }
}
