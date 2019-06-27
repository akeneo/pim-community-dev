<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Asset\Integration\Persistence;

use Akeneo\Asset\Component\Model\ChannelVariationsConfiguration;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;

class MassUploadAssetsInManyProductIntegration extends TestCase
{
    /**
     * @see jira PIM-8444
     * Given the job queue is already consuming message.
     * If I edit several products and upload in each of them an asset,
     * then when the queue will consume jobs which import those assets and link them to the product,
     * assets need to be linked to the good product.
     */
    public function testToUploadSeveralFilesThenLaunchAllImport()
    {
        $uploadContext = new UploadContext($this->getParameter('tmp_storage_dir'), 'admin');
        $originDir = __DIR__ . '/../../Common/images/%s';

        $importer = $this->get('pimee_product_asset.upload_importer');
        $queueLauncher = new JobLauncher(static::$kernel);
        $jobPublisher = $this->get('akeneo_batch_queue.launcher.queue_job_launcher');
        $jobInstance = $this->get('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier('apply_assets_mass_upload_into_asset_collection');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $fs = new Filesystem();

        $this->initializeCatalog();

        // I edit a product uploading a new "mugs" asset in the attribute. The job is published.
        $originMugFilePath = sprintf($originDir, 'mugs.jpg');
        $uploadedMugFilePath = $uploadContext->getTemporaryUploadDirectory() . '/mugs.jpg';
        $fs->copy($originMugFilePath, $uploadedMugFilePath);
        $importer->import($uploadContext);
        $mugJobExecution = $jobPublisher->launch(
            $jobInstance,
            $user,
            [
                'user_to_notify' => $user->getUsername(),
                'entity_type' => 'product',
                'entity_identifier' => 'mug_product',
                'attribute_code' => 'my_asset',
                'is_user_authenticated' => true,
                'imported_file_names' => ['mugs.jpg']
            ]
        );

        // I edit another product uploading a new "shoe" asset in the attribute. The job is published.
        $originShoeFilePath = sprintf($originDir, 'shoe.jpg');
        $targetShoeFilePath = $uploadContext->getTemporaryUploadDirectory() . '/shoe.jpg';
        $fs->copy($originShoeFilePath, $targetShoeFilePath);
        $importer->import($uploadContext);
        $shoeJobExecution = $jobPublisher->launch(
            $jobInstance,
            $user,
            [
                'user_to_notify' => $user->getUsername(),
                'entity_type' => 'product',
                'entity_identifier' => 'shoe_product',
                'attribute_code' => 'my_asset',
                'is_user_authenticated' => true,
                'imported_file_names' => ['shoe.jpg']
            ]
        );

        // The queue was full. Jobs are consumed once the queue is ready.
        $queueLauncher->launchConsumerOnce();
        $queueLauncher->waitCompleteJobExecution($mugJobExecution);
        $queueLauncher->launchConsumerOnce();
        $queueLauncher->waitCompleteJobExecution($shoeJobExecution);

        $warningRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository(Warning::class);
        $this->assertCount(0, $warningRepository->findAll());

        // Now assets need to be linked to the good product.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $productRepo = $this->get('pim_catalog.repository.product');

        $mugProduct = $productRepo->findOneByIdentifier('mug_product');
        $mugRawValues = $mugProduct->getRawValues();
        Assert::assertArrayHasKey('my_asset', $mugRawValues);
        Assert::assertSame(
            [
                '<all_channels>' => [
                    '<all_locales>' => ['mugs']
                ]
            ],
            $mugRawValues['my_asset']
        );

        $shoeProduct = $productRepo->findOneByIdentifier('shoe_product');
        $shoeRawValues = $shoeProduct->getRawValues();
        Assert::assertArrayHasKey('my_asset', $shoeRawValues);
        Assert::assertSame(
            [
                '<all_channels>' => [
                    '<all_locales>' => ['shoe']
                ]
            ],
            $shoeRawValues['my_asset']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $uploadContext = new UploadContext($this->getParameter('tmp_storage_dir'), 'admin');
        $uploadDir = $uploadContext->getTemporaryUploadDirectory();
        $importDir = $uploadContext->getTemporaryImportDirectory();

        $uploadedMugFilePath = $uploadDir . '/mugs.jpg';
        if ($fs->exists($uploadedMugFilePath)) {
            $fs->remove($uploadedMugFilePath);
        }

        $importedMugPath = $importDir . '/mugs.jpg';
        if ($fs->exists($importedMugPath)) {
            $fs->remove($importedMugPath);
        }

        $targetShoeFilePath = $uploadDir . '/shoe.jpg';
        if ($fs->exists($targetShoeFilePath)) {
            $fs->remove($targetShoeFilePath);
        }

        $importedShoePath = $importDir . '/shoe.jpg';
        if ($fs->exists($importedShoePath)) {
            $fs->remove($importedShoePath);
        }

        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     */
    private function initializeCatalog()
    {
        $this->createChannelConfiguration();
        $this->createAttribute('my_asset');
        $this->createFamily([
            'code' => 'family_with_asset',
            'attributes' => ['sku', 'my_asset'],
            'attribute_requirements' => ['ecommerce' => ['sku']]
        ]);
        $this->createProduct('mug_product', ['family' => 'family_with_asset']);
        $this->createProduct('shoe_product', ['family' => 'family_with_asset']);
    }

    private function createChannelConfiguration()
    {
        $channelConfSaver = $this->get('pimee_product_asset.saver.channel_configuration');
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $conf = new ChannelVariationsConfiguration();
        $conf->setChannel($channel);
        $conf->setConfiguration(['scale' => ['ratio' => 50]]);

        $channelConfSaver->save($conf);
    }

    private function createProduct(string $identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    private function createAttribute(string $code): void
    {
        $data = [
            'code' => $code,
            'type' => 'pim_assets_collection',
            'localizable' => false,
            'scopable' => false,
            'group' => 'other',
            'reference_data_name' => 'assets',
        ];

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $data = [])
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
