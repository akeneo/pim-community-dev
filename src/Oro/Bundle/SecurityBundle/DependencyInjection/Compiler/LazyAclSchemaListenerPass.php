<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * With symfony/security-acl 3.5 and DBAL 3, building the ACL schema queries the database. The Doctrine
 * event manager instantiates every listener as soon as one of them is needed, so without this pass any
 * command (including cache:warmup) would require a database connection. The listener is only used on
 * postGenerateSchema, so it is made lazy.
 */
final class LazyAclSchemaListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('security.acl.dbal.schema_listener')) {
            $container->getDefinition('security.acl.dbal.schema_listener')->setLazy(true);
        }
    }
}
