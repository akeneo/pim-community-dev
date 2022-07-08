<?php

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle;

use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\RegisterReferenceDataConfigurationsPass;
use Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\DependencyInjection\Compiler\VolumeQueryPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogVolumeMonitoringBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new VolumeQueryPass());
    }
}
