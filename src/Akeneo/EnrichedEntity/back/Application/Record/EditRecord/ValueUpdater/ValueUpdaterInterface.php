<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\AbstractEditValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

interface ValueUpdaterInterface
{
    public function supports(AbstractEditValueCommand $command): bool;

    public function __invoke(Record $record, AbstractEditValueCommand $command): void;
}
