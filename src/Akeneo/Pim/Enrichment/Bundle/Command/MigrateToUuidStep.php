<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

interface MigrateToUuidStep
{
    public function getMissingCount(OutputInterface $output): int;

    public function addMissing(OutputInterface $output): void;
}
