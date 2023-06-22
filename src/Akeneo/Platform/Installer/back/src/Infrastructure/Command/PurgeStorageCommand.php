<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Domain\Service\StoragePurgerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PurgeStorageCommand extends Command
{
    protected static $defaultName = 'pim:installer:purge_storage';
    protected static $defaultDescription = 'Purge storage.';

    public function __construct(
        private readonly StoragePurgerInterface $storagePurger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeStart = microtime(true);
        $this->storagePurger->execute();
        $timeEnd = microtime(true);
        $timeExecution = $timeEnd - $timeStart;

        $output->writeln(sprintf('Purge execution time : %s', $timeExecution));

        return Command::SUCCESS;
    }
}
