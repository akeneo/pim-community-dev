<?php

namespace Pim\Bundle\NotificationBundle\Factory;

use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Registry for notification factories
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationFactoryRegistry implements NotificationFactoryRegistryInterface
{
    /** @var JobNotificationFactoryInterface[] */
    protected $factories = [];

    /**
     * {@inheritdoc}
     */
    public function registerJobNotificationFactory(JobNotificationFactoryInterface $factory)
    {
        if (!in_array($factory, $this->factories)) {
            $this->factories[] = $factory;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJobNotificationFactory($jobType)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supportsJobType($jobType)) {
                return $factory;
            }
        }

        throw new NotImplementedException(
            sprintf('No notification factory found for the "%s" job type')
        );
    }
}
