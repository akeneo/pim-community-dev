<?php

namespace Pim\Bundle\CatalogVolumeMonitoringBundle;

use Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterUpdateGuessersPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterVersionPurgerAdvisorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogVolumeMonitoringBundle extends Bundle
{
}
