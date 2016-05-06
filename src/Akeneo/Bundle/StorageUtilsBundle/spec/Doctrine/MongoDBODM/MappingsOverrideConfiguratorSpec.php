<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class MappingsOverrideConfiguratorSpec extends ObjectBehavior
{
    function let(
        DocumentManager $dm,
        Configuration $configuration
    ) {
        $dm->getConfiguration()->willReturn($configuration);
    }

    function it_configures_the_mappings_of_an_original_model_that_is_override($configuration) {

        $metadataInfo = new ClassMetadataInfo('Foo\Bar\OriginalQux');
        $metadataInfo->mapOneReference(['fieldName' => 'relation1', 'targetEntity' => 'Foo']);
        $metadataInfo->mapManyReference(['fieldName' => 'relation2', 'targetEntity' => 'Foo']);
        $metadataInfo->mapOneEmbedded(['fieldName' => 'relation3', 'targetEntity' => 'Foo', 'mappedBy' => 'baz']);
        $metadataInfo->mapManyEmbedded(['fieldName' => 'relation4', 'targetEntity' => 'Foo']);

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
        $metadataInfo->getName()->willReturn($overrideQux1);
        $mappingDriver->loadMetadataForClass($originalQux1, Argument::any())->shouldBeCalled();

        $overrides = [
            ['original' => $originalQux1, 'override' => $overrideQux1],
            ['original' => $originalQux2, 'override' => $overrideQux2],
        ];

        $this->configure($metadataInfo, $overrides, $configuration);
    }

    public function getMatchers()
    {
        return [
            'beAnOverrideModel' => function($subject) {
                $mappings = $subject->associationMappings;

                return $subject->isMappedSuperclass &&
                    0 === count($mappings);
            },
        ];
    }
}

class OriginalQux1 {}
class OriginalQux2 {}
class OverrideQux1 extends OriginalQux1 {}
class OverrideQux2 extends OriginalQux2 {}
