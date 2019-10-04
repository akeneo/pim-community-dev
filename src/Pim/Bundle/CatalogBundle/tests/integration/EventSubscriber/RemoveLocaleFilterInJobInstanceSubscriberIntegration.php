<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Test\Integration\TestCase;
use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Model\ChannelInterface;

class RemoveLocaleFilterInJobInstanceSubscriberIntegration extends TestCase
{
    public function testRemoveOneLocaleFromAChannel()
    {
        $jobInstance = $this->createJobInstanceWithLocaleFilter('job1', 'tablet', ['en_US', 'fr_FR', 'de_DE']);

        $rawParameters = $jobInstance->getRawParameters();
        $locales = $rawParameters['filters']['structure']['locales'];
        sort($locales);
        $this->assertSame(['de_DE', 'en_US', 'fr_FR'], $locales);

        $channel = $this->removeFrenchFromTabletChannel();
        $this->getFromTestContainer('pim_catalog.saver.channel')->save($channel);

        $rawParameters = $this->getJobParameters($jobInstance);

        $this->assertSame(['de_DE', 'en_US'], $rawParameters['filters']['structure']['locales']);
    }

    public function testRemoveMultipleLocalesOnMultipleChannels()
    {
        $jobInstance1 = $this->createJobInstanceWithLocaleFilter('job1', 'tablet', ['en_US', 'fr_FR', 'de_DE']);
        $jobInstance2 = $this->createJobInstanceWithLocaleFilter('job2', 'ecommerce_china', ['en_US', 'zh_CN']);

        $rawParameters = $jobInstance1->getRawParameters();
        $locales = $rawParameters['filters']['structure']['locales'];
        sort($locales);
        $this->assertSame(['de_DE', 'en_US', 'fr_FR'], $locales);

        $rawParameters = $jobInstance2->getRawParameters();
        $locales = $rawParameters['filters']['structure']['locales'];
        sort($locales);
        $this->assertSame(['en_US', 'zh_CN'], $locales);

        $tabletChannel = $this->removeFrenchFromTabletChannel();
        $ecommerceChinaChannel = $this->removeEnglishFromEcommerceChinaChannel();

        $this->getFromTestContainer('pim_catalog.saver.channel')->saveAll([$tabletChannel, $ecommerceChinaChannel]);

        $rawParameters = $this->getJobParameters($jobInstance1);
        $this->assertSame(['de_DE', 'en_US'], $rawParameters['filters']['structure']['locales']);

        $rawParameters = $this->getJobParameters($jobInstance2);
        $this->assertSame(['zh_CN'], $rawParameters['filters']['structure']['locales']);
    }

    private function createJobInstanceWithLocaleFilter(string $jobCode, string $scope, array $locales)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getFromTestContainer('doctrine.orm.default_entity_manager');

        $jobInstance = new JobInstance('connector', JobInstance::TYPE_EXPORT, 'job_name');
        $jobInstance->setCode($jobCode);
        $jobInstance->setLabel($jobCode);
        $jobInstance->setRawParameters([
            'filters' => [
                'structure' => [
                    'scope' => $scope,
                    'locales' => $locales,
                ],
            ],
        ]);
        $entityManager->persist($jobInstance);
        $entityManager->flush();

        return $jobInstance;
    }

    private function removeFrenchFromTabletChannel(): ChannelInterface
    {
        $repository = $this->getFromTestContainer('pim_catalog.repository.channel');
        $channel = $repository->findOneByIdentifier('tablet');

        $data = [
            'locales' => ['en_US', 'de_DE'],
        ];

        $this->getFromTestContainer('pim_catalog.updater.channel')->update($channel, $data);

        return $channel;
    }

    private function removeEnglishFromEcommerceChinaChannel(): ChannelInterface
    {
        $repository = $this->getFromTestContainer('pim_catalog.repository.channel');
        $channel = $repository->findOneByIdentifier('ecommerce_china');

        $data = [
            'locales' => ['zh_CN'],
        ];

        $this->getFromTestContainer('pim_catalog.updater.channel')->update($channel, $data);

        return $channel;
    }

    private function getJobParameters(JobInstance $jobInstance): array
    {
        $sql = <<<SQL
SELECT raw_parameters
FROM akeneo_pim.akeneo_batch_job_instance
WHERE id = :jobId
SQL;
        $stmt = $this->getFromTestContainer('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->bindValue('jobId', $jobInstance->getId());
        $stmt->execute();
        $rawParameters = unserialize($stmt->fetchColumn(0));

        return $rawParameters;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
