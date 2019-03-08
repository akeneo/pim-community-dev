<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MappingsOverrideConfiguratorSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $em,
        Configuration $configuration
    ) {
        $em->getConfiguration()->willReturn($configuration);
    }

    function it_configures_the_mappings_of_an_original_model_that_is_override($configuration)
    {
        $metadataInfo = new ClassMetadataInfo('Foo\Bar\OriginalQux');
        $metadataInfo->mapManyToMany(['fieldName' => 'relation1', 'targetEntity' => 'Foo']);
        $metadataInfo->mapManyToOne(['fieldName' => 'relation2', 'targetEntity' => 'Foo']);
        $metadataInfo->mapOneToMany(['fieldName' => 'relation3', 'targetEntity' => 'Foo', 'mappedBy' => 'baz']);
        $metadataInfo->mapOneToOne(['fieldName' => 'relation4', 'targetEntity' => 'Foo']);

        $overrides = [
            ['original' => 'Foo\Bar\OriginalQux', 'override' => 'Acme\Bar\OverrideQux'],
            ['original' => 'Foo\Baz\OriginalQux', 'override' => 'Acme\Baz\OverrideQux'],
        ];

        $this->configure($metadataInfo, $overrides, $configuration)->shouldBeAnOverrideModel();
    }

    function it_configures_the_mappings_of_a_model_that_overrides_an_original_model(
        $configuration,
        ClassMetadataInfo $metadataInfo,
        MappingDriver $mappingDriver
    ) {
        $originalQux1 = __NAMESPACE__ . '\OriginalQux1';
        $originalQux2 = __NAMESPACE__ . '\OriginalQux2';
        $overrideQux1 = __NAMESPACE__ . '\OverrideQux1';
        $overrideQux2 = __NAMESPACE__ . '\OverrideQux2';

        $mappingDriver->getAllClassNames()->willReturn([$originalQux1]);
        $configuration->getMetadataDriverImpl()->willReturn($mappingDriver);
        $configuration->getNamingStrategy()->willReturn(null);
        $metadataInfo->getName()->willReturn($overrideQux1);
        $mappingDriver->loadMetadataForClass($originalQux1, Argument::any())->shouldBeCalled();

        $overrides = [
            ['original' => $originalQux1, 'override' => $overrideQux1],
            ['original' => $originalQux2, 'override' => $overrideQux2],
        ];

        $this->configure($metadataInfo, $overrides, $configuration);
    }

    public function getMatchers(): array
    {
        return [
            'beAnOverrideModel' => function ($subject) {
                $mappings = $subject->getAssociationMappings();

                return $subject->isMappedSuperclass && empty($mappings);
            },
        ];
    }
}

class OriginalQux1
{
}
class OriginalQux2
{
}
class OverrideQux1 extends OriginalQux1
{
}
class OverrideQux2 extends OriginalQux2
{
}
