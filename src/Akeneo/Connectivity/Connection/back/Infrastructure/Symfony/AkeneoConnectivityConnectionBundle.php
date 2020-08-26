<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Symfony;

use Akeneo\Connectivity\Connection\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterWebhookEventDataBuilderPass;
use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoConnectivityConnectionBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterSerializerPass('akeneo_connectivity.connection.serializer'))
            ->addCompilerPass(new RegisterWebhookEventDataBuilderPass());
    }
}
