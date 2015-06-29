<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Registry interface for notification factories
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NotificationFactoryRegistryInterface
{
    /**
     * Register a job notification factory
     *
     * @param JobNotificationFactoryInterface $factory
     */
    public function registerJobNotificationFactory(JobNotificationFactoryInterface $factory);

    /**
     * Return the a compatible factory for the specified job type
     *
     * @param string $jobType
     *
     * @throws NotImplementedException If no factory is found
     *
     * @return JobNotificationFactoryInterface
     */
    public function getJobNotificationFactory($jobType);
}
