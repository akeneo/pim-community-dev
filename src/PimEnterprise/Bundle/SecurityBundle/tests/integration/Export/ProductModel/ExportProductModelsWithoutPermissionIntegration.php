<?php

namespace PimEnterprise\Bundle\SecurityBundle\tests\integration\Export\ProductModel;

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
class ExportProductModelsWithoutPermissionIntegration extends AbstractProductModelExportTestCase
{
    public function testProductModelsWithoutPermissionApplied()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-ecommerce;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-ecommerce-CNY;a_scopable_price-ecommerce-EUR;a_scopable_price-ecommerce-USD
root_product_model_visible_for_redactor;familyVariantA1;;categoryA2;;;;;;;;;;;;;;;;;;;
root_product_model_visible_for_manager_only;familyVariantA1;;categoryB;;;;;;;;;;;;;;;;;;;

CSV;

        $csv = $this->jobLauncher->launchExport('csv_product_model_export');

        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductModelsViewableByRedactorWithQueueJobLauncher()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. JobLauncher::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => 'csv_product_model_export']);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');

        $expectedCsv = <<<CSV
code;family_variant;parent;categories;an_image;a_date;a_file;a_localizable_image-en_US;a_localized_and_scopable_text_area-en_US-ecommerce;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-ecommerce-CNY;a_scopable_price-ecommerce-EUR;a_scopable_price-ecommerce-USD
root_product_model_visible_for_redactor;familyVariantA1;;categoryA2;;;;;;;;;;;;;;;;;;;
root_product_model_visible_for_manager_only;familyVariantA1;;categoryB;;;;;;;;;;;;;;;;;;;

CSV;

        $config = [
            'filePath' => $filePath,
        ];

        $jobExecution = $this->get('akeneo_batch_queue.launcher.queue_job_launcher')->launch($jobInstance, $user, $config);

        while ($this->jobLauncher->hasJobInQueue()) {
            $this->jobLauncher->launchConsumerOnce();
        }

        $this->jobLauncher->waitCompleteJobExecution($jobExecution);

        $csv = file_get_contents($filePath);

        $this->assertSame($expectedCsv, $csv);
    }
}
