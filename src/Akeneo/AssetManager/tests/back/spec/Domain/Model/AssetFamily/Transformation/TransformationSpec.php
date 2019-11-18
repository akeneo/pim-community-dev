<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use PhpSpec\ObjectBehavior;

class TransformationSpec extends ObjectBehavior
{
    function it_creates_a_transformation(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('targetIdentifier');

        $source->getChannelIdentifierAsString()->willReturn(null);
        $target->getChannelIdentifierAsString()->willReturn(null);

        $source->getLocaleIdentifierAsString()->willReturn(null);
        $target->getLocaleIdentifierAsString()->willReturn(null);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);
    }

    // TODO add more tests loke this
    function it_throws_an_exception_if_target_is_equal_to_source(Source $source, Target $target)
    {
        $source->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');
        $target->getAttributeIdentifierAsString()->willReturn('sourceIdentifier');

        $source->getChannelIdentifierAsString()->willReturn(null);
        $target->getChannelIdentifierAsString()->willReturn(null);

        $source->getLocaleIdentifierAsString()->willReturn(null);
        $target->getLocaleIdentifierAsString()->willReturn(null);

        $this->beConstructedThrough('create', [
            $source,
            $target,
            OperationCollection::create([]),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
