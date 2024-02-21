<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

class AkeneoFeatureFlagBundleSpec extends ObjectBehavior
{
    function it_replaces_with_alternative_if_feature_disabled(FeatureFlags $flags)
    {
        $dic = new ContainerBuilder();
        $dic->set('feature_flags', $flags->getWrappedObject());

        $dic->setDefinition('service', (new Definition(Service::class))->addTag('feature_flags.is_enabled', [
            'feature' => 'feature_a',
            'otherwise' => 'alternative',
        ]));
        $dic->setDefinition('alternative', (new Definition(Alternative::class)));

        $this->process($dic);
        $this->setContainer($dic);

        $flags->isEnabled('feature_a')->willReturn(false);
        $this->boot();

        if (!$dic->get('service') instanceof Alternative) {
            throw new \Exception('service should be an instance of Alternative');
        }
    }

    function it_replaces_with_real_service_if_feature_enabled(FeatureFlags $flags)
    {
        $dic = new ContainerBuilder();
        $dic->set('feature_flags', $flags->getWrappedObject());

        $dic->setDefinition('service', (new Definition(Service::class))->addTag('feature_flags.is_enabled', [
            'feature' => 'feature_a',
            'otherwise' => 'alternative',
        ]));
        $dic->setDefinition('alternative', (new Definition(Alternative::class)));

        $this->process($dic);
        $this->setContainer($dic);

        $flags->isEnabled('feature_a')->willReturn(true);
        $this->boot();

        if (!$dic->get('service') instanceof Service) {
            throw new \Exception('service should be an instance of Service');
        }
    }

    function it_keeps_service_synthetic_if_disabled_and_no_alternative(FeatureFlags $flags)
    {
        $dic = new ContainerBuilder();
        $dic->set('feature_flags', $flags->getWrappedObject());

        $dic->setDefinition('service', (new Definition(Service::class))->addTag('feature_flags.is_enabled', [
            'feature' => 'feature_a',
        ]));
        $dic->setDefinition('alternative', (new Definition(Alternative::class)));

        $this->process($dic);
        $this->setContainer($dic);

        $flags->isEnabled('feature_a')->willReturn(false);
        $this->boot();

        if (!$dic->getDefinition('service')->isSynthetic()) {
            throw new \Exception('service should be synthetic');
        }
    }
}

class Service {}
class Alternative {}
