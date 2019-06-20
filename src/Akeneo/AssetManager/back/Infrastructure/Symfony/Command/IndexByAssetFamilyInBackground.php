<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\ReferenceEntity\Application\Record\Subscribers\IndexByReferenceEntityInBackgroundInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Tool\Component\Console\CommandLauncher;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexByReferenceEntityInBackground implements IndexByReferenceEntityInBackgroundInterface
{
    /** @var  CommandLauncher */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    public function execute(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $cmd = sprintf(
            '%s %s',
            IndexRecordsCommand::INDEX_RECORDS_COMMAND_NAME,
            (string) $referenceEntityIdentifier
        );

        $this->commandLauncher->executeBackground($cmd, '/dev/null');
    }
}
