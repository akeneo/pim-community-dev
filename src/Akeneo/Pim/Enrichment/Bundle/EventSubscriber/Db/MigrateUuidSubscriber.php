<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidCommand;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateUuidSubscriber implements EventSubscriberInterface
{
    public function __construct(private MigrateToUuidCommand $migrateToUuidCommand)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => ['migrateToUuid', 1000],
        ];
    }

    public function migrateToUuid(): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        try {
            $this->migrateToUuidCommand->run($input, $output);
        } catch (\Throwable $e) {
            print_r(
                'Unable to complete the migration' . PHP_EOL .
                'Error:' . $e->getMessage() . PHP_EOL
            );
            throw $e;
        }
    }
}
