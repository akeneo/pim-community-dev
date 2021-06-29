<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Query\Sql;

use Akeneo\Channel\Bundle\Query\Sql\IsChannelUsedInProductProductExportJob;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsChannelUsedInProductProductExportJobIntegration extends TestCase
{
    public function test_it_determines_if_a_channel_is_used_in_product_export_jobs(): void
    {
        $isChannelUsedInProductProductExportJob = $this->get(IsChannelUsedInProductProductExportJob::class);

        $this->assertFalse($isChannelUsedInProductProductExportJob->execute('mobile'));

        $this->givenAProductImportJob();
        $this->assertFalse($isChannelUsedInProductProductExportJob->execute('mobile'));

        $this->givenAProductExportJobUsingChannel('ecommerce');
        $this->assertFalse($isChannelUsedInProductProductExportJob->execute('mobile'));

         $this->givenAProductExportJobUsingChannel('mobile');
        $this->assertTrue($isChannelUsedInProductProductExportJob->execute('mobile'));
    }

    private function givenAProductImportJob(): void
    {
        $job =  $this->get('pim_connector.factory.job_instance')->create()
            ->setCode('an_import_job')
            ->setConnector('Akeneo CSV Connector')
            ->setType('import')
            ->setJobName('csv_product_import')
            ->setRawParameters([
                'filePath' => '/tmp/export_products.csv',
                'delimiter' => ';',
                'filter' => [
                    'structure' => [
                        'scope' => 'mobile',
                        'locales' => ['en_US', 'fr_FR']
                    ]
                ]
            ]);

        $this->get('akeneo_batch.saver.job_instance')->save($job);
    }

    private function givenAProductExportJobUsingChannel(string $channelCode): void
    {
        $job =  $this->get('pim_connector.factory.job_instance')->create()
            ->setCode(sprintf('an_export_using_channel_%s', $channelCode))
            ->setConnector('Akeneo CSV Connector')
            ->setType('export')
            ->setJobName('csv_product_export')
            ->setRawParameters([
                'filePath' => '/tmp/export_products.csv',
                'delimiter' => ';',
                'withHeader' => true,
                'filter' => [
                    'data' => [
                        [
                            'field' => 'enabled',
                            'operator'=> '=',
                            'value' => true,
                        ]
                    ],
                    'structure' => [
                        'scope' => $channelCode,
                        'locales' => ['en_US', 'fr_FR']
                    ]
                ]
            ]);

        $this->get('akeneo_batch.saver.job_instance')->save($job);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
