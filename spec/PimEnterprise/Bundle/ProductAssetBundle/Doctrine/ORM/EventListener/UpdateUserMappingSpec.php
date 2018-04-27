<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\EventListener\UpdateUserMapping;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

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
            "targetEntity" => CategoryInterface::class,
        ])->shouldBeCalled();

        $this->loadClassMetadata($eventArgs)->shouldReturn(null);
    }

    function it_does_not_add_doctrine_mapping_for_other_entities(
        LoadClassMetadataEventArgs $eventArgs,
        ClassMetadata $classMetadata
    ) {
        $eventArgs->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn('Pim\Component\User\Model\RolesInterface');

        $classMetadata->mapField()->shouldNotBeCalled();
        $classMetadata->mapManyToOne()->shouldNotBeCalled();

        $this->loadClassMetadata($eventArgs)->shouldReturn(null);
    }
}
