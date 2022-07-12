<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CredentialsEncrypterPass implements CompilerPassInterface
{
    const CREDENTIALS_ENCRYPTER_TAG = 'pim_import_export.credentials_encrypter';

    public function process(ContainerBuilder $container)
    {
        $registry = $container->get('Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry');
        $taggedServices = $container->findTaggedServiceIds(self::CREDENTIALS_ENCRYPTER_TAG);

        foreach ($taggedServices as $id => $attributes) {
            $registry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
