<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
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
        Target $target2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);

        $target1->getAttributeIdentifierAsString()->willReturn('same_target_attribute');
        $target2->getAttributeIdentifierAsString()->willReturn('same_target_attribute');

        $target1->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $target2->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        
        $target1->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));
        $target2->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));

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
        Source $source2
    ) {
        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $transformation1->getSource()->willReturn($source1);
        $transformation2->getSource()->willReturn($source2);

        $target1->getAttributeIdentifierAsString()->willReturn('same_attribute');
        $source1->getAttributeIdentifierAsString()->willReturn('source_attribute');

        $target2->getAttributeIdentifierAsString()->willReturn('target_attribute');
        $source2->getAttributeIdentifierAsString()->willReturn('same_attribute');

        $target1->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $source2->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));

        $target1->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));
        $source2->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
