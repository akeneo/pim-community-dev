<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class TransformationSpec extends ObjectBehavior
{
    function it_creates_a_transformation(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('targetIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::noReference());
        $target->getChannelReference()->willReturn(ChannelReference::noReference());

        $source->getLocaleReference()->willReturn(LocaleReference::noReference());
        $target->getLocaleReference()->willReturn(LocaleReference::noReference());

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);
        $this->getWrappedObject();
    }

    function it_creates_a_transformation_on_same_scopable_attribute(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $target->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')));

        $source->getLocaleReference()->willReturn(LocaleReference::noReference());
        $target->getLocaleReference()->willReturn(LocaleReference::noReference());

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);
        $this->getWrappedObject();
    }


    function it_creates_a_transformation_on_same_localizable_attribute(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $target->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));

        $source->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));
        $target->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')));

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);
        $this->getWrappedObject();
    }

    function it_throws_an_exception_if_target_is_equal_to_source(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::noReference());
        $target->getChannelReference()->willReturn(ChannelReference::noReference());

        $source->getLocaleReference()->willReturn(LocaleReference::noReference());
        $target->getLocaleReference()->willReturn(LocaleReference::noReference());

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_throws_an_exception_if_target_is_equal_to_source_with_a_scope(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $target->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));

        $source->getLocaleReference()->willReturn(LocaleReference::noReference());
        $target->getLocaleReference()->willReturn(LocaleReference::noReference());

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_throws_an_exception_if_target_is_equal_to_source_with_a_channel(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::noReference());
        $target->getChannelReference()->willReturn(ChannelReference::noReference());

        $source->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));
        $target->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }

    function it_throws_an_exception_if_target_is_equal_to_source_with_a_scope_and_a_channel(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $target->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));

        $source->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));
        $target->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(new \InvalidArgumentException('A transformation can not have the same source and target'))->duringInstantiation();
    }
}
