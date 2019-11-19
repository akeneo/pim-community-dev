<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use PhpSpec\ObjectBehavior;

class TransformationCollectionSpec extends ObjectBehavior
{
    function it_creates_a_transformation_collection(
        Transformation $transformation,
        Target $target,
        Source $source
    ) {
        $transformation->getTarget()->willReturn($target);
        $transformation->getSource()->willReturn($source);
        $target->getAttributeIdentifierAsString()->willReturn('target');
        $source->getAttributeIdentifierAsString()->willReturn('source');
        $this->beConstructedThrough('create', [[$transformation]]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_a_collection_item_is_not_a_transformation(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $target1->getAttributeIdentifierAsString()->willReturn('target_attribute_1');
        $target2->getAttributeIdentifierAsString()->willReturn('target_attribute_2');

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                new \stdClass(),
                $transformation2,
            ],
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_2_transformations_have_the_same_target(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2,
        ChannelReference $channelReference1,
        ChannelReference $channelReference2,
        LocaleReference $localeReference1,
        LocaleReference $localeReference2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $target1->getAttributeIdentifierAsString()->willReturn('target_attribute');
        $target2->getAttributeIdentifierAsString()->willReturn('target_attribute');
        $target1->getChannelReference()->willReturn($channelReference1);
        $target2->getChannelReference()->willReturn($channelReference2);
        $channelReference1->equals($channelReference2)->willReturn(true);
        $target1->getLocaleReference()->willReturn($localeReference1);
        $target2->getLocaleReference()->willReturn($localeReference2);
        $localeReference1->equals($localeReference2)->willReturn(true);

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_a_source_is_a_target_of_another_transformation(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2,
        Source $source1,
        Source $source2,
        ChannelReference $channelReferenceTarget1,
        ChannelReference $channelReferenceSource2,
        LocaleReference $localeReferenceTarget1,
        LocaleReference $localeReferenceSource2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $transformation1->getSource()->willReturn($source1);
        $transformation2->getSource()->willReturn($source2);

        $target1->getAttributeIdentifierAsString()->willReturn('same_attribute');
        $source1->getAttributeIdentifierAsString()->willReturn('source_attribute');
        $target2->getAttributeIdentifierAsString()->willReturn('target_attribute');
        $source2->getAttributeIdentifierAsString()->willReturn('same_attribute');
        $target1->getChannelReference()->willReturn($channelReferenceTarget1);
        $source2->getChannelReference()->willReturn($channelReferenceSource2);
        $channelReferenceTarget1->equals($channelReferenceSource2)->willReturn(true);
        $target1->getLocaleReference()->willReturn($localeReferenceTarget1);
        $source2->getLocaleReference()->willReturn($localeReferenceSource2);
        $localeReferenceTarget1->equals($localeReferenceSource2)->willReturn(true);

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
