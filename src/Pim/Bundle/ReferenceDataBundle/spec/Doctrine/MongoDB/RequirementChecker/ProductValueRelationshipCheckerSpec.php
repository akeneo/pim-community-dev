<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\RequirementChecker;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class ProductValueRelationshipCheckerSpec extends ObjectBehavior
{
    public function let(DocumentManager $em, ClassMetadataInfo $classMetadata)
    {
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $this->beConstructedWith($em, 'spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue');
    }

    function it_checks_a_valid_many_to_one_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getFieldMapping('color')->willReturn([
            'type' => 'entity',
            'isOwningSide' => true,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_a_valid_many_to_many_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getFieldMapping('fabrics')->willReturn([
            'type' => 'entities',
            'isOwningSide' => true,
            'idsField' => 'fabricIds'
        ]);

        $classMetadata->getFieldMapping('fabricIds')->willReturn([
            'type' => 'collection'
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_a_non_existent_mapping_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('foo');
        $classMetadata->getFieldMapping('foo')->willThrow(
            MappingException::mappingNotFound('spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue', 'foo')
        );

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            "No mapping found for field 'foo' in class ".
            "'spec\\Pim\\Bundle\\ReferenceDataBundle\\RequirementChecker\\CustomValidProductValue'."
        );
    }

    function it_checks_an_invalid_many_to_one_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getFieldMapping('color')->willReturn([
            'type' => 'invalid',
            'isOwningSide' => true,
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_SIMPLE);
        $configuration->getName()->willReturn('color');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure the type and the owning side correctly in your '.
            '"spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue" "color" relation. ' .
            'You can take the relation "option" as example.'
        );
    }

    function it_checks_an_invalid_many_to_many_collection_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getFieldMapping('fabrics')->willReturn([
            'type' => 'entities',
            'isOwningSide' => true,
        ]);

        $classMetadata->getFieldMapping('fabricIds')->willReturn([
            'type' => 'invalid'
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure the "idsField" in your ' .
            '"spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue" "fabrics" relation. ' .
            'You can take the relation "options" as example.'
        );
    }

    function it_checks_an_invalid_many_to_many_relationship($classMetadata, ConfigurationInterface $configuration)
    {
        $classMetadata->getFieldMapping('fabrics')->willReturn([
            'type' => 'entities',
            'isOwningSide' => false,
            'idsField' => 'fabricIds'
        ]);

        $classMetadata->getFieldMapping('fabricIds')->willReturn([
            'type' => 'collection'
        ]);

        $configuration->getType()->willReturn(ConfigurationInterface::TYPE_MULTI);
        $configuration->getName()->willReturn('fabrics');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure the type and the owning side correctly in your ' .
            '"spec\Pim\Bundle\ReferenceDataBundle\RequirementChecker\CustomValidProductValue" "fabrics" relation. ' .
            'You can take the relation "options" as example.'
        );
    }
}
