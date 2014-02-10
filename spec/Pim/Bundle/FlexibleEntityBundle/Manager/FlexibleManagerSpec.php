<?php

namespace spec\Pim\Bundle\FlexibleEntityBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleEntityRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class FlexibleManagerSpec extends ObjectBehavior
{
    const ENTITY_CLASS    = 'Flexible\\Class\\Entity';
    const VALUE_CLASS     = 'Flexible\\Class\\Value';
    const ATTRIBUTE_CLASS = 'Flexible\\Class\\Attribute';
    const OPTION_CLASS    = 'Flexible\\Class\\Option';
    const OPT_VALUE_CLASS = 'Flexible\\Class\\Value';

    function let(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        ClassMetadataInfo $flexibleMeta,
        ClassMetadataInfo $valueMeta,
        ClassMetadataInfo $attributeMeta,
        ClassMetadataInfo $optionMeta,
        ClassMetadataInfo $optionValueMeta,
        FlexibleEntityRepositoryInterface $repository
    ) {
        $objectManager->getClassMetadata(self::ENTITY_CLASS)->willReturn($flexibleMeta);
        $flexibleMeta->getAssociationMappings()->willReturn(['values' => ['targetEntity' => self::VALUE_CLASS]]);
        $objectManager->getClassMetadata(self::VALUE_CLASS)->willReturn($valueMeta);
        $valueMeta->getAssociationMappings()->willReturn(['attribute' => ['targetEntity' => self::ATTRIBUTE_CLASS]]);
        $objectManager->getClassMetadata(self::ATTRIBUTE_CLASS)->willReturn($attributeMeta);
        $attributeMeta->getAssociationMappings()->willReturn(['options' => ['targetEntity' => self::OPTION_CLASS]]);
        $objectManager->getClassMetadata(self::OPTION_CLASS)->willReturn($optionMeta);
        $optionMeta->getAssociationMappings()->willReturn(['optionValues' => ['targetEntity' => self::OPT_VALUE_CLASS]]);

        $objectManager->getRepository(self::ENTITY_CLASS)->willReturn($repository);

        $this->beConstructedWith(self::ENTITY_CLASS, $objectManager, $eventDispatcher);
    }

    function it_stores_flexible_config()
    {
        $this->getFlexibleConfig()->shouldReturn(
            [
                'flexible_class'               => self::ENTITY_CLASS,
                'flexible_value_class'         => self::VALUE_CLASS,
                'attribute_class'              => self::ATTRIBUTE_CLASS,
                'attribute_option_class'       => self::OPTION_CLASS,
                'attribute_option_value_class' => self::OPT_VALUE_CLASS,
            ]
        );
    }

    function its_init_mode_is_required_attributes()
    {
        $this->getFlexibleInitMode()->shouldReturn('required_attributes');
    }

    function it_has_a_locale()
    {
        $this->setLocale('fr');
        $this->getLocale()->shouldReturn('fr');
    }

    function it_has_a_scope()
    {
        $this->setScope('ecommerce');
        $this->getScope()->shouldReturn('ecommerce');
    }

    function it_has_an_object_manager($objectManager)
    {
        $this->getObjectManager()->shouldReturn($objectManager);
    }

    function it_has_a_flexible_FQCN()
    {
        $this->getFlexibleName()->shouldReturn(self::ENTITY_CLASS);
    }

    function it_has_an_attribute_FQCN()
    {
        $this->getAttributeName()->shouldReturn(self::ATTRIBUTE_CLASS);
    }

    function it_has_an_attribute_option_FQCN()
    {
        $this->getAttributeOptionName()->shouldReturn(self::OPTION_CLASS);
    }

    function it_has_an_attribute_option_value_FQCN()
    {
        $this->getAttributeOptionValueName()->shouldReturn(self::OPT_VALUE_CLASS);
    }

    function it_has_a_flexible_repository($repository)
    {
        $this->getFlexibleRepository()->shouldReturn($repository);
    }

    function it_has_an_attribute_option_repository(EntityRepository $repository, $objectManager)
    {
        $objectManager->getRepository(self::OPTION_CLASS)->willReturn($repository);

        $this->getAttributeOptionRepository()->shouldReturn($repository);
    }
}
