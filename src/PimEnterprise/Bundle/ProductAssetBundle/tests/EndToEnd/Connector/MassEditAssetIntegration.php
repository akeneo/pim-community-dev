<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\EndToEnd\Connector;

use Akeneo\Component\Batch\Model\Warning;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class MassEditAssetIntegration extends TestCase
{
    public function testToMassClassifyMoreAssetsThanBatchSize()
    {
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

        $jobExecution = $this->get('akeneo_batch_queue.launcher.queue_job_launcher')->launch($jobInstance, $user, $config);

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

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

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
