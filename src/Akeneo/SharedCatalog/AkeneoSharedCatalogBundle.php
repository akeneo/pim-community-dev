<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\SharedCatalog;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkeneoSharedCatalogBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        // @TODO RAC-267
        $isEnabled = (bool)($_ENV['FLAG_SHARED_CATALOG_ENABLED'] ?? false);

        if (!$isEnabled) {
            $this->removeSharedCatalogJobProfile();
        }
    }

    private function removeSharedCatalogJobProfile()
    {
        $jobName = $this->container->getParameter('akeneo.shared_catalog.connector.code');
        $jobType = $this->container->getParameter('pim_connector.job.export_type');
        $connector = $this->container->getParameter('akeneo.shared_catalog.connector.name');

        $this->container->get('akeneo_batch.job.job_registry')->remove($jobName, $jobType, $connector);
    }
}
