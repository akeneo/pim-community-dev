<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\EventListener\MongoDBODM;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * @require Doctrine\ODM\MongoDB\Events
 */
class ResolveTargetEntityListenerSpec extends ObjectBehavior
{
    function it_load_class_metadata(LoadClassMetadataEventArgs $args, ClassMetadata $classMetadata)
    {
        $args->getClassMetadata()->willReturn($classMetadata);
        $mappings = [
            'item' => [
                'targetEntity' => 'Akeneo\Model\ItemInterface',
                'fieldName'    => 'item'
            ]
        ];

        $classMetadata->fieldMappings = $mappings;
        $this->addResolveTargetEntity('Akeneo\Model\ItemInterface', 'Akeneo\Model\Item', []);
        $this->loadClassMetadata($args);
    }
}
