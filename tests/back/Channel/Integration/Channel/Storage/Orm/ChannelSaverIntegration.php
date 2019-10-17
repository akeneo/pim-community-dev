<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Storage\Orm;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Saver\ChannelSaverInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelSaverIntegration extends TestCase
{
    public function test_that_it_saves_a_new_channel(): void
    {
        $channel = $this->updateChannel(
            $this->channelFactory()->create(),
            [
                'code' => 'mobile',
                'locales' => ['en_US'],
                'category_tree' => 'master',
                'currencies' => ['USD'],
            ]
        );
        $createdNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $this->getSaver()->save($channel);
        $savedNormalizedChannel = $this->getNormalizer()->normalize(
            $this->getRepository()->findOneByIdentifier('mobile')
        );

        self::assertEquals($createdNormalizedChannel, $savedNormalizedChannel);
    }

    public function test_that_it_saves_an_existing_channel_by_removing_a_locale_from_this_channel(): void
    {
        $channel = $this->getRepository()->findOneByIdentifier('ecommerce');
        $notUpdatedNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $channel = $this->updateChannel($channel, ['locales' => ['fr_FR']]);
        $updatedNormalizedChannel = $this->getNormalizer()->normalize($channel);

        $this->getSaver()->save($channel);
        $savedNormalizedChannel = $this->getNormalizer()->normalize(
            $this->getRepository()->findOneByIdentifier('ecommerce')
        );

        self::assertNotEquals($notUpdatedNormalizedChannel, $savedNormalizedChannel);
        self::assertEquals($updatedNormalizedChannel, $savedNormalizedChannel);

        // @see https://github.com/akeneo/pim-community-dev/issues/10828
        // kill background process because you can have a race condition:
        // - this test triggers the asynchronous job pim:catalog:remove-completeness-for-channel-and-locale and then the test finishes (but not the job)
        // - then table are cleaned in the next test with the fixture loader
        // - then the pim:catalog:remove-completeness-for-channel-and-locale insert data into a table
        // - then the dump is loaded to load the fixtures of the next test
        // - INSERT INTO of this dump fails because the data inserted by "pim:catalog:remove-completeness-for-channel-and-locale" already exists
        //
        // ideally, we should not trigger this asynchronous job and test it differently
        exec('pkill -f "pim:catalog:remove-completeness-for-channel-and-locale"');
    }

    public function test_it_updates_job_instances_when_a_channel_as_a_changed_category_tree(): void
    {
        $this->withJobForChannelWithCategory('import', 'my_beautiful_job_instance', 'ecommerce', 'master');
        $this->withCategory('another_category');
        /** @var ChannelInterface $channel */
        $channel = $this->getRepository()->findOneByIdentifier('ecommerce');
        $this->updateChannel($channel, ['category_tree' => 'another_category']);
        $this->getSaver()->save($channel);
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => 'my_beautiful_job_instance']);

        if (null === $jobInstance) {
            throw new \InvalidArgumentException('The Job instance does not exist');
        }

        $isUpdated = array_reduce($jobInstance->getRawParameters()['filters']['data'], function (bool $isUpdated , array $data) {
            return $isUpdated || $data['field'] === 'categories' && in_array('another_category', $data['value']);
        }, false);

        Assert::assertTrue($isUpdated, 'The job instance  has not been updated');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function updateChannel(ChannelInterface $channel, array $data): ChannelInterface
    {
        $updater = $this->get('pim_catalog.updater.channel');
        $validator = $this->get('validator');

        $updater->update($channel, $data);
        $violations = $validator->validate($channel);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }

        return $channel;
    }

    private function channelFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.channel');
    }

    private function getRepository(): ChannelRepositoryInterface
    {
        return $this->get('pim_catalog.repository.channel');
    }

    private function getNormalizer(): NormalizerInterface
    {
        return $this->get('pim_catalog.normalizer.standard.channel');
    }

    private function getSaver(): ChannelSaverInterface
    {
        return $this->get('pim_catalog.saver.channel');
    }

    private function withJobForChannelWithCategory(string $jobType, string $jobCode, string $channelCode, string $categoryCode)
    {
        /** @var JobInstanceFactory $jobInstanceFactory */
        $jobInstanceFactory = $this->get('akeneo_batch.job_instance_factory');
        $jobInstance = $jobInstanceFactory->createJobInstance($jobType);
        $jobInstance->setRawParameters([
            'filters' => [
                'data' => [[
                    'field' => 'categories',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value' => [$categoryCode]
                ]],
                'structure' => [
                    'scope' => $channelCode
                ]
            ]
        ]);
        $jobInstance->setJobName('csv_product_export');
        $jobInstance->setLabel('a_label');
        $jobInstance->setCode($jobCode);
        $jobInstance->setConnector('a_connector');
        $jobInstanceValidator = $this->get('validator');

        $violations = $jobInstanceValidator->validate($jobInstance);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }
        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);
    }

    private function withCategory(string $categoryCode): CategoryInterface
    {
        return $this->createCategory(['code' => $categoryCode, 'parent' => null]);
    }
}
