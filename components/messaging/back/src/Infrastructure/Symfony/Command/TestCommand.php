<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Message\LaunchProductAndProductModelEvaluationsMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TestCommand extends Command
{
    protected static $defaultName = 'akeneo:test';

    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = new LaunchProductAndProductModelEvaluationsMessage('toto');
        $this->bus->dispatch($message);

        echo "Done\n";

        return 0;
    }
}
