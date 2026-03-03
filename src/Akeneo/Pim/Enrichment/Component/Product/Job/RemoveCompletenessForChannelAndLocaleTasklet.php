<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
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
        private ProductRepositoryInterface $productRepository,
        private ChannelRepositoryInterface $channelRepository,
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
        if (!$this->shouldRun()) {
            return;
        }

        $usersToNotify = [$this->stepExecution->getJobParameters()->get('username')];
        $this->notifyUsersItBegins($usersToNotify);

        $productIdentifiers = $this->productQueryBuilderFactory->create()->execute();
        $productUuidsToClean = [];
        /** @var IdentifierResult $productIdentifier */
        foreach ($productIdentifiers as $productIdentifier) {
            $productUuidsToClean[] = \preg_replace('/^product_/', '', $productIdentifier->getId());

            if (count($productUuidsToClean) >= $this->productBatchSize) {
                $products = $this->productRepository->getItemsFromUuids($productUuidsToClean);
                $this->cleanProducts($products);
                $this->cacheClearer->clear();
                $productUuidsToClean = [];
            }
        }

        if (!empty($productUuidsToClean)) {
            $products = $this->productRepository->getItemsFromUuids($productUuidsToClean);
            $this->cleanProducts($products);
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

    private function shouldRun(): bool
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $channelCode = $jobParameters->get('channel_code');
        /** @var ?ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            return true;
        }

        $localeCodes = $jobParameters->get('locales_identifier');
        $currentLocaleCodes = $channel->getLocales()->map(
            static fn (LocaleInterface $locale): string => $locale->getCode()
        );
        foreach ($localeCodes as $localeCode) {
            if (!$currentLocaleCodes->contains($localeCode)) {
                return true;
            }
        }

        return false;
    }
}
