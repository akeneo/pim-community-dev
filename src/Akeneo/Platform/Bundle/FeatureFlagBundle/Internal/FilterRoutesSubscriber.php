<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Filter routes from feature flags that are disabled.
 *
 * Exactly the same mechanism that https://github.com/bestit/flagception-bundle/blob/master/src/Listener/RoutingMetadataSubscriber.php
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterRoutesSubscriber implements EventSubscriberInterface
{
    const FEATURE_KEY = '_feature';

    /** @var FeatureFlags */
    private $featureFlags;

    public function __construct(FeatureFlags $featureFlags)
    {
        $this->featureFlags = $featureFlags;
    }

    public function filterRoutesFromDisabledFeatureFlags(ControllerEvent $event)
    {
        if (!$event->getRequest()->attributes->has(static::FEATURE_KEY)) {
            return;
        }

        $feature = $event->getRequest()->attributes->get(static::FEATURE_KEY);
        if (!$this->featureFlags->isEnabled($feature)) {
            // Maybe we'll need to throw a 403 instead.
            // But the system could be enhanced with a key "_throw" for instance in the route configuration.
            // Let's keep it simple at the moment.
            throw new NotFoundHttpException(sprintf('Feature "%s" is not enabled.', $feature));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'filterRoutesFromDisabledFeatureFlags',
        ];
    }
}
