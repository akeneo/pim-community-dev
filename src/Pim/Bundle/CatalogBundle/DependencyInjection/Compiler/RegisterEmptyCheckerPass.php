<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged empty checker in the registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterEmptyCheckerPass implements CompilerPassInterface
{
    /** @staticvar int The default provider priority */
    const DEFAULT_PRIORITY = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_catalog.empty_checker.%s.chained';

    /** @staticvar string */
    const CHECKER_TAG = 'pim_catalog.empty_checker.%s';

    /** @var string */
    protected $emptyCheckerType;

    /**
     * @param $emptyCheckerType
     */
    public function __construct($emptyCheckerType)
    {
        $this->emptyCheckerType = $emptyCheckerType;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(sprintf(static::REGISTRY_ID, $this->emptyCheckerType))) {
            return;
        }

        $registryDefinition = $container->getDefinition(
            sprintf(static::REGISTRY_ID, $this->emptyCheckerType)
        );

        $checkers = [];
        $tag = sprintf(static::CHECKER_TAG, $this->emptyCheckerType);
        foreach ($container->findTaggedServiceIds($tag) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                if (!isset($checkers[$priority])) {
                    $checkers[$priority] = [];
                }

                $checkers[$priority][] = new Reference($serviceId);
            }
        }

        ksort($checkers);
        foreach ($checkers as $unsortedProviders) {
            foreach ($unsortedProviders as $checker) {
                $registryDefinition->addMethodCall('addEmptyChecker', [$checker]);
            }
        }
    }
}
