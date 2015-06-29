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
class NotificationFactoryRegistry
{
    /** @var NotificationFactoryInterface[] */
    protected $factories = [];

    /**
     * {@inheritdoc}
     */
    public function register(NotificationFactoryInterface $factory)
    {
        if (!in_array($factory, $this->factories)) {
            $this->factories[] = $factory;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($type)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($type)) {
                return $factory;
            }
        }

        throw new NotImplementedException(
            sprintf('No notification factory found for the "%s" job type')
        );
    }
}
