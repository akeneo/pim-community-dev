<?php

namespace Akeneo\Test\IntegrationTestsBundle;

use Akeneo\Test\IntegrationTestsBundle\DependencyInjection\MakeServicesPublicForTestEnv;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for the integration tests, allowing to have a DI in the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoIntegrationTestsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MakeServicesPublicForTestEnv(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
