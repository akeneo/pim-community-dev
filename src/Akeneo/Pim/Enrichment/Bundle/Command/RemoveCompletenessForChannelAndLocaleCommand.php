<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class RemoveCompletenessForChannelAndLocaleCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setName('pim:catalog:remove-completeness-for-channel-and-locale')
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
        $productBatchSize    = $this->getContainer()->getParameter('pim_job_product_batch_size');
        $cacheClearer        = $this->getContainer()->get('pim_connector.doctrine.cache_clearer');
        $notifier            = $this->getContainer()->get('pim_notification.notifier');
        $notificationFactory = $this->getContainer()->get('pim_notification.factory.notification');
        $rootDir             = $this->getContainer()->get('kernel')->getRootDir();
        $env                 = $this->getContainer()->get('kernel')->getEnvironment();

        $output->writeln(
            sprintf(
                '<info>[%s] Locales "%s" are removed from channel "%s". ' .
                'Removing all related completenesses from products.</info>',
                $this->getCurrentDatetime(),
                $input->getArgument('locales-identifier'),
                $channelCode
            )
        );

        $pushNotif = $notificationFactory->create();
        $pushNotif
            ->setType('warning')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.start')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $notifier->notify($pushNotif, [$username]);
        $output->writeln(
            sprintf(
                '<info>[%s] User "%s" has been notified completenesses removal started.</info>',
                $this->getCurrentDatetime(),
                $username
            )
        );

        $io = new SymfonyStyle($input, $output);
        $products = $pqbFactory = $this->getContainer()
            ->get('pim_catalog.query.product_query_builder_factory')
            ->create()
            ->execute();

        $io->progressStart($products->count());
        $productsToClean = [];
        foreach ($products as $product) {
            $productsToClean[] = $product->getIdentifier();

            if (count($productsToClean) >= $productBatchSize) {
                $this->launchCleanTask($productsToClean, $env, $rootDir);
                $cacheClearer->clear();
                $io->progressAdvance(count($productsToClean));
                $productsToClean = [];
            }
        }

        if (!empty($productsToClean)) {
            $this->launchCleanTask($productsToClean, $env, $rootDir);
            $io->progressAdvance(count($productsToClean));
        }
        $io->progressFinish();

        $channel = $this->getContainer()->get('pim_catalog.repository.channel')
            ->findOneByIdentifier($channelCode);

        $locales = $this->getContainer()->get('pim_catalog.repository.locale')
            ->findBy(['code' => $localesIdentifiers]);
        foreach ($locales as $locale) {
            $locale->removeChannel($channel);
        }

        if (!empty($locales)) {
            $this->getContainer()->get('pim_catalog.saver.locale')->saveAll($locales);
        }
        $output->writeln(
            sprintf('<info>[%s] Related products completenesses removal done.</info>', $this->getCurrentDatetime())
        );

        $doneNotif = $notificationFactory->create();
        $doneNotif
            ->setType('success')
            ->setMessage('pim_enrich.notification.settings.remove_completeness_for_channel_and_locale.done')
            ->setContext([
                'actionType' => 'settings',
                'showReportButton' => false
            ]);
        $notifier->notify($doneNotif, [$username]);
        $output->writeln(
            sprintf(
                '<info>[%s] User "%s" has been notified completenesses removal is finished.</info>',
                $this->getCurrentDatetime(),
                $username
            )
        );
    }

    /**
     * Return current datetime with 'Y-m-d H:i:s' format.
     *
     * @return string
     */
    private function getCurrentDatetime(): string
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
