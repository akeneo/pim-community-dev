<?php

namespace Akeneo\Bundle\StorageUtilsBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\StorageMappingsPass;
use Akeneo\Bundle\StorageUtilsBundle\MongoDB\CustomTypeRegisterer;
//TODO: should be trashed
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Akeneo storage utils bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoStorageUtilsBundle extends Bundle
{
    public function __construct()
    {
        CustomTypeRegisterer::register();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        if (class_exists(StorageMappingsPass::DOCTRINE_MONGODB_MAPPINGS_PASS)) {
            // TODO	(2014-05-09 19:42 by Gildas): Remove service registration when
            // https://github.com/doctrine/DoctrineMongoDBBundle/pull/197 is merged
            $definition = $container->register(
                'doctrine_mongodb.odm.listeners.resolve_target_document',
                'Doctrine\ODM\MongoDB\Tools\ResolveTargetDocumentListener'
            );
            $definition->addTag('doctrine_mongodb.odm.event_listener', array('event' => 'loadClassMetadata'));
        }
    }
}
