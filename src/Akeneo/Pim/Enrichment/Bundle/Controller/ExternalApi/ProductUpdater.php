<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

interface ProductUpdater
{
    public function update(array $data): void;
}
