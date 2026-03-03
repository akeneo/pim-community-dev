<?php

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass is useful to create a list of volume queries and the associated feature flag required to
 * make it available in the catalog volume monitoring screen.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VolumeQueryPass implements CompilerPassInterface
{
    /** @staticvar string */
    const VOLUME_NORMALIZER_SERVICE = 'pim_volume_monitoring.volume.normalizer.volumes';

    /** @staticvar string */
    const COUNT_QUERY_TAG = 'pim_volume_monitoring.persistence.count_query';

    /** @staticvar string */
    const AVERAGE_MAX_QUERY_TAG = 'pim_volume_monitoring.persistence.average_max_query';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->register($container, self::COUNT_QUERY_TAG, 'addCountVolumeQuery');
        $this->register($container, self::AVERAGE_MAX_QUERY_TAG, 'addAverageMaxVolumeQuery');
    }

    private function register(ContainerBuilder $container, string $tagName, string $functionName)
    {
        $volumeNormalizer = $container->getDefinition(self::VOLUME_NORMALIZER_SERVICE);

        $taggedServices = $container->findTaggedServiceIds($tagName);
        foreach ($taggedServices as $id => $attributes) {
            $attributes = current($attributes);
            $feature = $attributes['feature'] ?? null;

            $volumeNormalizer->addMethodCall($functionName, [new Reference($id), $feature]);
        }
    }
}
