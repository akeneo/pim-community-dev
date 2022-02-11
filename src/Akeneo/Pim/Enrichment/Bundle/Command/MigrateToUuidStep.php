<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

interface MigrateToUuidStep
{
    public function getMissingCount(): int;

    public function addMissing(bool $dryRun, OutputInterface $output): void;

    public function shouldBeExecuted(): bool;

    public function getDescription(): string;
}
