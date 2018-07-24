<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler\AddPermissionFilterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimPermissionBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddPermissionFilterPass());
        $container->addCompilerPass(new RegisterSerializerPass('pimee_authorization_serializer'));
    }
}
