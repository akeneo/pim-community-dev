<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoctrineTargetDocumentResolver extends AbstractDoctrineTargetResolver
{
    /**
     * {@inheritdoc}
     */
    protected function getResolverDefinitionKey()
    {
        return 'doctrine_mongodb.odm.listeners.resolve_target_document';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResolverMethod()
    {
        return 'addResolveTargetDocument';
    }
}
