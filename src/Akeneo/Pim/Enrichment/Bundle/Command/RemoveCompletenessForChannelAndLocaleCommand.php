<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Command to remove completeness for channel and locale.
 *
 * @see https://akeneo.atlassian.net/browse/PIM-7155
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCompletenessForChannelAndLocaleCommand extends Command
{
    protected static $defaultName = 'pim:catalog:remove-completeness-for-channel-and-locale';

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /** @var NotifierInterface */
    private $notifier;

    /** @var SimpleFactoryInterface */
    private $notificationFactory;

    /** @var string */
    private $rootDir;

    /** @var string */
    private $env;

    /** @var int */
    private $productBatchSize;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var SaverInterface */
    private $channelSaver;

    public function __construct(
        EntityManagerClearerInterface $cacheClearer,
        NotifierInterface $notifier,
        SimpleFactoryInterface $notificationFactory,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        SaverInterface $channelSaver,
        string $rootDir,
        string $env,
        int $productBatchSize
    ) {
        parent::__construct();

        $this->cacheClearer = $cacheClearer;
        $this->notifier = $notifier;
        $this->notificationFactory = $notificationFactory;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->rootDir = $rootDir;
        $this->env = $env;
        $this->productBatchSize = $productBatchSize;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->channelSaver = $channelSaver;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setDescription('When a channel is updated, products completenesses related to channel and locales need to be cleaned.')
            ->addArgument(
                'channel-code',
                InputArgument::REQUIRED,
                'Channel code'
            )
            ->addArgument(
                'locales-identifier',
                InputArgument::REQUIRED,
                'locales codes separated by ","'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'user to notify'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localesIdentifiers  = explode(',', $input->getArgument('locales-identifier'));
        $channelCode         = $input->getArgument('channel-code');
        $username            = $input->getArgument('username');

        $output->writeln(
            sprintf(
                '<info>[%s] Locales "%s" are removed from channel "%s". ' .
                'Removing all related completenesses from products.</info>',
                $this->getCurrentDateTime(),
                $input->getArgument('locales-identifier'),
                $channelCode
            )
        );

        $pushNotif = $this->notificationFactory->create();
        $pushNotif
            ->setType('warning')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.start')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $this->notifier->notify($pushNotif, [$username]);
        $output->writeln(
            sprintf(
                '<info>[%s] User "%s" has been notified completenesses removal started.</info>',
                $this->getCurrentDateTime(),
                $username
            )
        );

        $io = new SymfonyStyle($input, $output);
        $products = $pqbFactory = $this->productQueryBuilderFactory
            ->create()
            ->execute();

        $io->progressStart($products->count());
        $productsToClean = [];
        foreach ($products as $product) {
            $productsToClean[] = $product->getIdentifier();

            if (count($productsToClean) >= $this->productBatchSize) {
                $this->launchCleanTask($productsToClean, $this->env, $this->rootDir);
                $this->cacheClearer->clear();
                $io->progressAdvance(count($productsToClean));
                $productsToClean = [];
            }
        }

        if (!empty($productsToClean)) {
            $this->launchCleanTask($productsToClean, $this->env, $this->rootDir);
            $io->progressAdvance(count($productsToClean));
        }
        $io->progressFinish();

        $channel = $this->channelRepository
            ->findOneByIdentifier($channelCode);

        $locales = $this->localeRepository
            ->findBy(['code' => $localesIdentifiers]);
        foreach ($locales as $locale) {
            $locale->removeChannel($channel);
        }

        if (!empty($locales)) {
            $this->channelSaver->saveAll($locales);
        }
        $output->writeln(
            sprintf('<info>[%s] Related products completenesses removal done.</info>', $this->getCurrentDateTime())
        );

        $doneNotif = $this->notificationFactory->create();
        $doneNotif
            ->setType('success')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.done')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $this->notifier->notify($doneNotif, [$username]);
        $output->writeln(
            sprintf(
                '<info>[%s] User "%s" has been notified completenesses removal is finished.</info>',
                $this->getCurrentDateTime(),
                $username
            )
        );
    }

    /**
     * Return current datetime with 'Y-m-d H:i:s' format.
     *
     * @return string
     */
    private function getCurrentDateTime(): string
    {
        $datetime = new \DateTime('now');

        return $datetime->format('Y-m-d H:i:s');
    }

    /**
     * Lanches the clean command on given identifiers, channel and locale
     *
     * @param array            $productIdentifiers
     * @param string           $env
     * @param string           $rootDir
     */
    private function launchCleanTask(
        array $productIdentifiers,
        string $env,
        string $rootDir
    ) {
        $process = new Process([
            sprintf('%s/../bin/console', $rootDir),
            'pim:product:refresh',
            sprintf('--env=%s', $env),
            implode(',', $productIdentifiers)
        ]);
        $process->run();
    }
}
