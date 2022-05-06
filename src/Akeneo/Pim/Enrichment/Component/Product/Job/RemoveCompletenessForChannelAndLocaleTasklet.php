<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * When a channel is updated, products completenesses related to channel and locales need to be cleaned.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCompletenessForChannelAndLocaleTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private EntityManagerClearerInterface $cacheClearer,
        private NotifierInterface $notifier,
        private SimpleFactoryInterface $notificationFactory,
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        private CursorableRepositoryInterface $productRepository,
        private ChannelRepositoryInterface $channelRepository,
        private LocaleRepositoryInterface $localeRepository,
        private BulkSaverInterface $localeBulkSaver,
        private BulkSaverInterface $productBulkSaver,
        private int $productBatchSize
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $localesIdentifiers = $jobParameters->get('locales_identifier');
        $channelCode = $jobParameters->get('channel_code');

        $usersToNotify = [$jobParameters->get('username')];
        $this->notifyUsersItBegins($usersToNotify);

        $productIdentifiers = $this->productQueryBuilderFactory->create()->execute();
        $productIdentifiersToClean = [];
        /** @var IdentifierResult $productIdentifier */
        foreach ($productIdentifiers as $productIdentifier) {
            $productIdentifiersToClean[] = $productIdentifier->getIdentifier();

            if (count($productIdentifiersToClean) >= $this->productBatchSize) {
                $products = $this->productRepository->getItemsFromIdentifiers($productIdentifiersToClean);
                $this->cleanProducts($products);
                $this->cacheClearer->clear();
                $productIdentifiersToClean = [];
            }
        }

        if (!empty($productIdentifiersToClean)) {
            $products = $this->productRepository->getItemsFromIdentifiers($productIdentifiersToClean);
            $this->cleanProducts($products);
        }
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        $locales = $this->localeRepository->findBy(['code' => $localesIdentifiers]);
        foreach ($locales as $locale) {
            $locale->removeChannel($channel);
        }

        if (!empty($locales)) {
            $this->localeBulkSaver->saveAll($locales);
        }
        $this->notifyUsersItIsDone($usersToNotify);
    }

    private function cleanProducts(array $products): void
    {
        $this->productBulkSaver->saveAll($products, ['force_save' => true]);
    }

    private function notifyUsersItBegins(array $users): void
    {
        $pushNotif = $this->notificationFactory->create();
        $pushNotif
            ->setType('warning')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.start')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $this->notifier->notify($pushNotif, $users);
    }

    private function notifyUsersItIsDone(array $users): void
    {
        $doneNotif = $this->notificationFactory->create();
        $doneNotif
            ->setType('success')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.done')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $this->notifier->notify($doneNotif, $users);
    }
}
