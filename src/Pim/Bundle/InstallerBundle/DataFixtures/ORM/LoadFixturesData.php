<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Pim\Bundle\InstallerBundle\DataFixtures\AbstractLoadFixturesData;

/**
 * Load fixtures data
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadFixturesData extends AbstractLoadFixturesData
{
    /**
     * {@inheritdoc}
     */
    protected function getLaunchableJobs()
    {
        $jobs = $this->getAllJobs();
        foreach ($jobs as $key => $job) {
            // Do not load products and associations with the ORM fixtures when MongoDB support is activated
            $storageDriver = $this->container->getParameter('pim_catalog_product_storage_driver');
            if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $storageDriver
                && 1 === preg_match('#^fixtures_(product|association)_(csv|yml)$#', $job->getCode())) {
                unset($jobs[$key]);
            }

            // Do not load job when fixtures file is not available
            if (!is_readable($job->getRawConfiguration()['filePath'])) {
                unset($jobs[$key]);
            }
        }

        return $jobs;
    }
}
