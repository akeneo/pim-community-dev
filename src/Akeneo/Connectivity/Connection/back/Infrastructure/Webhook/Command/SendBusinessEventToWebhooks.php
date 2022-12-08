<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\ConnectionLost;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendBusinessEventToWebhooks extends Command
{
    private const MYSQL_IS_UNAVAILABLE_ERROR_CODE = 2002;

    protected static $defaultName = 'akeneo:connectivity:send-business-event';
    protected static $defaultDescription = 'Send business event to webhooks';

    public function __construct(
        private BulkEventNormalizer $bulkEventNormalizer,
        private SendBusinessEventToWebhooksHandler $commandHandler,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Symfony Messenger serialized message'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = \json_decode($input->getArgument('message'), true);
        $event = $this->bulkEventNormalizer->denormalize($message, BulkEvent::class);

        try {
            $this->commandHandler->handle(new SendBusinessEventToWebhooksCommand($event));
            $this->eventDispatcher->dispatch(new MessageProcessedEvent());
        } catch (ConnectionException $exception) {
            if ($exception->getPrevious()?->getCode() === self::MYSQL_IS_UNAVAILABLE_ERROR_CODE) {
                $this->logger->warning('Mysql is unavailable', ['exception' => $exception]);

                return Command::FAILURE;
            } elseif ('SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' === $exception->getMessage()) {
                $this->logger->warning('MySQL server has gone away', ['exception' => $exception]);

                return Command::FAILURE;
            }

            throw $exception;
        } catch (IndexationException $exception) {
            if ('No alive nodes found in your cluster' === $exception->getMessage()) {
                $this->logger->warning('Elastic Search is unavailable', ['exception' => $exception]);

                return Command::FAILURE;
            }

            throw $exception;
        }

        return Command::SUCCESS;
    }
}
