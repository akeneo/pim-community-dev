<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Asset\EndToEnd\ExternalApi\Connector;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Model\Warning;

class MassEditAssetIntegration extends TestCase
{
    public function testToMassClassifyMoreAssetsThanBatchSize()
    {
        $this->createAdminUser();
        $assets = $this->createAssets();

        $ids = array_map(function (AssetInterface $asset) {
            return $asset->getId();
        }, $assets);

        $config = [
            'filters' => [['field' => 'id', 'operator' => Operators::IN_LIST, 'value' => $ids]],
            'actions' => [['field' => 'categories', 'value' => ['asset_main_catalog']]],
            'user_to_notify' => 'admin'
        ];

        $jobInstance = $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('classify_assets');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');

        $jobExecution = $this
            ->get('akeneo_batch_queue.launcher.queue_job_launcher')
            ->launch($jobInstance, $user, $config);

        $jobLauncher = new JobLauncher(static::$kernel);
        $jobLauncher->launchConsumerOnce();
        $jobLauncher->waitCompleteJobExecution($jobExecution);

        $warningRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository(Warning::class);
        $this->assertCount(0, $warningRepository->findAll());

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        foreach ($this->get('pimee_product_asset.repository.asset')->findAll() as $asset) {
            $category = $asset->getCategories()->first();
            $this->assertSame('asset_main_catalog', $category->getCode());
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return array
     */
    private function createAssets(): array
    {
        $assets = [];
        $count = $this->getParameter('pimee_mass_edit_asset_batch_size') + 1;
        for ($i = 0; $i <= $count; $i++) {
            $asset = $this->get('pimee_product_asset.factory.asset')->create();
            $this->get('pimee_product_asset.updater.asset')->update($asset, ['code' => 'asset_' . $i]);
            $assets[] = $asset;
        }

        $this->get('pimee_product_asset.saver.asset')->saveAll($assets);

        return $assets;
    }
}
