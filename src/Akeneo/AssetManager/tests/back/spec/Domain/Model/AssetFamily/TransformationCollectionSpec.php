<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
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
        $target->equals($source)->willReturn(false);

        $this->beConstructedThrough('create', [[$transformation]]);
        $this->beAnInstanceOf(TransformationCollection::class);
    }

    function it_throws_an_exception_when_a_collection_item_is_not_a_transformation(
        Transformation $transformation1,
        Transformation $transformation2
    ) {
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

        $target1->equals($target2)->willReturn(true);
        $transformation1->getLabel()->willReturn(TransformationLabel::fromString('label1'));
        $transformation2->getLabel()->willReturn(TransformationLabel::fromString('label2'));

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(new \InvalidArgumentException('You can not define 2 transformation with the same target'))->duringInstantiation();
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

        $target1->equals($source2)->willReturn(true);
        $target1->equals($target2)->willReturn(false);
        $transformation1->getLabel()->willReturn(TransformationLabel::fromString('label1'));
        $transformation2->getLabel()->willReturn(TransformationLabel::fromString('label2'));

        $this->beConstructedThrough('create', [
            [
                $transformation1,
                $transformation2
            ]
        ]);
        $this->shouldThrow(new \InvalidArgumentException('You can not define a transformation having a source as a target of another transformation'))->duringInstantiation();
    }

    function it_normalizes_a_transformation_collection(
        Transformation $transformation1,
        Transformation $transformation2,
        Target $target1,
        Target $target2,
        Source $source1,
        Source $source2
    ) {
        $this->beConstructedThrough('create', [[ $transformation1, $transformation2 ]]);

        $transformation1->getTarget()->willReturn($target1);
        $transformation2->getTarget()->willReturn($target2);
        $transformation1->getSource()->willReturn($source1);
        $transformation2->getSource()->willReturn($source2);
        $target1->equals($target2)->willReturn(false);
        $target1->equals($source2)->willReturn(false);
        $target2->equals($source1)->willReturn(false);

        $transformation1->getLabel()->willReturn(TransformationLabel::fromString('label1'));
        $transformation2->getLabel()->willReturn(TransformationLabel::fromString('label2'));

        $normalizedTransformation1 = ['key' => 'normalized transformation 1'];
        $normalizedTransformation2 = ['key' => 'normalized transformation 2'];

        $transformation1->normalize()->willReturn($normalizedTransformation1);
        $transformation2->normalize()->willReturn($normalizedTransformation2);

        $this->normalize()->shouldReturn([
            $normalizedTransformation1,
            $normalizedTransformation2,
        ]);
    }

    function it_returns_a_transformation_given_a_target(
        Transformation $transformation,
        Source $source,
        Target $target,
        Target $targetFilter
    ) {
        $transformation->getTarget()->willReturn($target);
        $transformation->getSource()->willReturn($source);
        $target->equals($source)->willReturn(false);
        $this->beConstructedThrough('create', [[$transformation]]);

        $target->equals($targetFilter)->willReturn(true);
        $this->getByTarget($targetFilter)->shouldReturn($transformation);
        $target->equals($targetFilter)->willReturn(false);
        $this->getByTarget($targetFilter)->shouldReturn(null);
    }

    function it_can_be_updated_based_on_another_collection()
    {
        $operation = ThumbnailOperation::create(['width' => 100, 'height' => 80]);

        $actualTransformation1 = Transformation::create(
            TransformationLabel::fromString('label1'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'en_US']),
            OperationCollection::create([$operation]),
            null,
            '_1',
            new \DateTime('2010-01-01')
        );
        $actualTransformation2 = Transformation::create(
            TransformationLabel::fromString('label2'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'fr_FR']),
            OperationCollection::create([$operation]),
            null,
            '_2',
            new \DateTime('2010-01-01')
        );
        $actualTransformation3 = Transformation::create(
            TransformationLabel::fromString('label3'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'de_DE']),
            OperationCollection::create([$operation]),
            null,
            '_3',
            new \DateTime('2010-01-01')
        );
        $this->beConstructedThrough('create', [[$actualTransformation1, $actualTransformation2, $actualTransformation3]]);

        $nonUpdatedTransformation = Transformation::create(
            TransformationLabel::fromString('label1'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'en_US']),
            OperationCollection::create([$operation]),
            null,
            '_1',
            new \DateTime('2010-06-30')
        );
        $newTransformation = Transformation::create(
            TransformationLabel::fromString('new_label'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'fr_FR']),
            OperationCollection::create([$operation]),
            null,
            '_2',
            new \DateTime('2010-01-01')
        );
        $updatedTransformation = Transformation::create(
            TransformationLabel::fromString('label3'),
            Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => 'de_DE']),
            OperationCollection::create([$operation]),
            null,
            '_new_suffix',
            new \DateTime('2011-01-01')
        );

        $this->update(TransformationCollection::create([$nonUpdatedTransformation, $newTransformation, $updatedTransformation]));
        $this->normalize()->shouldReturn([
            $actualTransformation1->normalize(),
            $newTransformation->normalize(),
            $updatedTransformation->normalize(),
        ]);
    }
}
