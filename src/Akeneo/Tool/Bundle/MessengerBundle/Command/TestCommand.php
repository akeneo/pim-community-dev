<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\LaunchEvaluationsCommand;
use Akeneo\Tool\Bundle\MessengerBundle\Registry\MessageHandlerRegistry;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message1;
use Akeneo\Tool\Component\Messenger\CorrelationAwareInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This command should be executed by the TraceableMessageBridgeHandler. On contrary of the handler, this
 * command is tenant aware.
 * The command receives a message and a consumer name, and based on the consumer name it executes the
 * right handler.
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TestCommand extends Command
{
    protected static $defaultName = 'akeneo:test-message';

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = new LaunchProductAndProductModelEvaluationsMessage(
            new \DateTimeImmutable(),
            ProductUuidCollection::fromProductUuids([
                ProductUuid::fromUuid(Uuid::uuid4()),
            ]),
            ProductModelIdCollection::fromProductModelIds([]),
            []
        );
        $this->bus->dispatch($message);

        return Command::SUCCESS;
    }
}
