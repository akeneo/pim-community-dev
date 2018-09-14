<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;

interface ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool;

    public function __invoke(Record $record, AbstractEditValueCommand $command): void;
}
