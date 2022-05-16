<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

interface ZddMigration
{
    public function migrate(): void;

    public function getName(): string;
}
