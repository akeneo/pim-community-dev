<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoctrineTargetEntityResolver extends AbstractDoctrineTargetResolver
{
    /**
     * {@inheritdoc}
     */
    protected function getResolverDefinitionKey()
    {
        return 'doctrine.orm.listeners.resolve_target_entity';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResolverMethod()
    {
        return 'addResolveTargetEntity';
    }
}
