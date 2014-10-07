<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged views in the view registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterViewsPass implements CompilerPassInterface
{
    /** @staticvar integer The default position for services */
    const DEFAULT_POSITION = 100;

    /** @staticvar integer The registry id */
    const REGISTRY_ID = 'pim_enrich.view.registry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            throw new \LogicException(
                sprintf('Resolver "%s" is called on an incorrect registry service id', get_class($this))
            );
        }

        $views = [];
        $views['tab'] = $this->findAndSortTaggedServices('pim_enrich_views.tab', $container);

        $container->getDefinition(static::REGISTRY_ID)->addMethodCall('setViews', [$views]);
    }

    /**
     * Returns an array of service references for a specified tag name
     *
     * @param string           $tagName
     * @param ContainerBuilder $container
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);

        $sortedServices = [];
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $position = isset($tag['position']) ? $tag['position'] : self::DEFAULT_POSITION;

                if (!isset($sortedServices[$tag['identifier']])) {
                    $sortedServices[$tag['identifier']] = [];
                }

                $sortedServices[$tag['identifier']][$position][] = new Reference($serviceId);
            }
        }

        foreach ($sortedServices as $identifier => $services) {
            ksort($sortedServices[$identifier]);
            $sortedServices[$identifier] = call_user_func_array('array_merge', $sortedServices[$identifier]);
        }

        return $sortedServices;
    }
}
