<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\RequirementChecker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Prophecy\Argument;

class ProductValueRelationshipCheckerSpec extends ObjectBehavior
{
    public function let(EntityManagerInterface $em, ClassMetadataInfo $classMetadata)
    {
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $this->beConstructedWith($em, 'spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue');
    }

    function it_checks_a_valid_many_to_one_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getAssociationMapping('color')->willReturn([
            'type' => ClassMetadataInfo::MANY_TO_ONE,
            'isOwningSide' => true,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_a_valid_many_to_many_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getAssociationMapping('fabrics')->willReturn([
            'type' => ClassMetadataInfo::MANY_TO_MANY,
            'isOwningSide' => true,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_a_non_existent_mapping_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $configuration->getName()->willReturn('foo');
        $classMetadata->getAssociationMapping('foo')->willThrow(
            MappingException::mappingNotFound('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue', 'foo')
        );

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            "No mapping found for field 'foo' on class ".
            "'spec\\Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\CustomValidProductValue'."
        );
    }

    function it_checks_an_invalid_many_to_one_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getAssociationMapping('color')->willReturn([
            'type' => ClassMetadataInfo::MANY_TO_MANY,
            'isOwningSide' => true,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure your "spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue" ' .
            'relation "color" correctly. You can take the relation "option" as example.'
        );
    }

    function it_checks_an_invalid_many_to_many_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getAssociationMapping('fabrics')->willReturn([
            'type' => ClassMetadataInfo::MANY_TO_MANY,
            'isOwningSide' => false,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure your "spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue" ' .
            'relation "fabrics" correctly. You can take the relation "options" as example.'
        );
    }
}
