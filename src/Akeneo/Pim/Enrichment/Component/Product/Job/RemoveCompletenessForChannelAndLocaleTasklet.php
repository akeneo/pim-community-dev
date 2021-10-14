<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Psr\Log\LoggerInterface;

/**
 * When a channel is updated, products completenesses related to channel and locales need to be cleaned.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCompletenessForChannelAndLocaleTasklet implements TaskletInterface
{
    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /** @var NotifierInterface */
    private $notifier;

    /** @var SimpleFactoryInterface */
    private $notificationFactory;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var BulkSaverInterface */
    private $localeBulkSaver;

    /** @var int */
    private $productBatchSize;

    /** @var StepExecution */
    private $stepExecution;

    /** @var BulkSaverInterface */
    private $productBulkSaver;

    public function __construct(
        EntityManagerClearerInterface $cacheClearer,
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        BulkSaverInterface $localeBulkSaver,
        BulkSaverInterface $productBulkSaver,
        int $productBatchSize
    ) {
        $this->cacheClearer = $cacheClearer;
        $this->notifier = $notifier;
        $this->notificationFactory = $notificationFactory;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->localeBulkSaver = $localeBulkSaver;
        $this->productBatchSize = $productBatchSize;
        $this->productBulkSaver = $productBulkSaver;
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

        $products = $this->productQueryBuilderFactory->create()->execute();
        $productsToClean = [];
        foreach ($products as $product) {
            $productsToClean[] = $product;

            if (count($productsToClean) >= $this->productBatchSize) {
                $this->cleanProducts($productsToClean);
                $this->cacheClearer->clear();
                $productsToClean = [];
            }
        }

        if (!empty($productsToClean)) {
            $this->cleanProducts($productsToClean);
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

    private function cleanProducts(array $productIdentifiers): void
    {
        $this->productBulkSaver->saveAll($productIdentifiers, ['force_save' => true]);
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
