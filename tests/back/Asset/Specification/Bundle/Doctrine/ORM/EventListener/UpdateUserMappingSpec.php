<?php

namespace Specification\Akeneo\Asset\Bundle\Doctrine\ORM\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Asset\Bundle\Doctrine\ORM\EventListener\UpdateUserMapping;
use Akeneo\Asset\Component\Model\CategoryInterface;

class UpdateUserMappingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateUserMapping::class);
    }

    function it_is_a_event_subscriber()
    {
        $this->shouldImplement(EventSubscriber::class);
    }

    function it_subscribes_to_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            Events::loadClassMetadata,
        ]);
    }

    function it_add_doctrine_mapping_for_user_entity(
        LoadClassMetadataEventArgs $eventArgs,
        ClassMetadata $classMetadata
    ) {
        $eventArgs->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn(UserInterface::class);

        $classMetadata->mapField([
            'fieldName' => 'assetDelayReminder',
            'type' => 'integer',
            'options' => [
                'default' => 5
            ],
        ])->shouldBeCalled();

        $classMetadata->mapField([
            'fieldName' => 'proposalsToReviewNotification',
            'type' => 'boolean',
            'options' => [
                'default' => true
            ],
        ])->shouldBeCalled();

        $classMetadata->mapField([
            'fieldName' => 'proposalsStateNotification',
            'type' => 'boolean',
            'options' => [
                'default' => true
            ],
        ])->shouldBeCalled();

        $classMetadata->mapManyToOne([
            'targetEntity' => CategoryInterface::class,
            'fieldName' => 'defaultAssetTree',
        ])->shouldBeCalled();

        $this->loadClassMetadata($eventArgs)->shouldReturn(null);
    }

    function it_does_not_add_doctrine_mapping_for_other_entities(
        LoadClassMetadataEventArgs $eventArgs,
        ClassMetadata $classMetadata
    ) {
        $eventArgs->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn(RoleInterface::class);

        $classMetadata->mapField()->shouldNotBeCalled();
        $classMetadata->mapManyToOne()->shouldNotBeCalled();

        $this->loadClassMetadata($eventArgs)->shouldReturn(null);
    }
}
