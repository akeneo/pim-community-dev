<?php

namespace spec\Akeneo\Component\FileTransformer\Transformation;

use Akeneo\Component\FileTransformer\Exception\AlreadyRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Transformation\TransformationInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransformationRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Akeneo\Component\FileTransformer\Transformation\TransformationRegistry');
    }

    function it_adds_transformations_to_the_registry_and_can_return_all_of_them(
        TransformationInterface $resizeTransformation,
        TransformationInterface $thumbnailTransformation
    ) {
        $resizeTransformation->getName()->willReturn('resize');
        $resizeTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $thumbnailTransformation->getName()->willReturn('thumbnail');
        $thumbnailTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $this->add($resizeTransformation);
        $this->all()->shouldReturn([
            'resize-image-jpeg'    => $resizeTransformation,
            'resize-image-tiff'    => $resizeTransformation
        ]);

        $this->add($thumbnailTransformation);
        $this->all()->shouldReturn([
            'resize-image-jpeg'    => $resizeTransformation,
            'resize-image-tiff'    => $resizeTransformation,
            'thumbnail-image-jpeg' => $thumbnailTransformation,
            'thumbnail-image-tiff' => $thumbnailTransformation
        ]);
    }

    function it_returns_a_specific_transformation(
        TransformationInterface $resizeTransformation
    ) {
        $resizeTransformation->getName()->willReturn('resize');
        $resizeTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $this->add($resizeTransformation);
        $this->get('resize', 'image/jpeg')->shouldReturn($resizeTransformation);
    }

    function it_tests_if_the_asked_transformation_is_registered(
        TransformationInterface $resizeTransformation
    ) {
        $resizeTransformation->getName()->willReturn('resize');
        $resizeTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $this->add($resizeTransformation);

        $this->has('resize', 'image/jpeg')->shouldReturn(true);
        $this->has('resize', 'image/png')->shouldReturn(false);
    }

    function it_throws_an_exception_if_the_asked_transformation_is_not_registered(
        TransformationInterface $resizeTransformation
    ) {
        $resizeTransformation->getName()->willReturn('resize');
        $resizeTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $this->add($resizeTransformation);

        $this->shouldThrow(
            new NonRegisteredTransformationException(
                'resize',
                'image/png',
                'No "resize" transformation registered for the mime type "image/png".'
            )
        )->duringGet('resize', 'image/png');
    }

    function it_throws_an_exception_if_it_try_to_add_a_transformation_already_registered(
        TransformationInterface $resizeTransformation
    ) {
        $resizeTransformation->getName()->willReturn('resize');
        $resizeTransformation->getSupportedMimeTypes()->willReturn(['image/jpeg', 'image/tiff']);

        $this->add($resizeTransformation);
        $this->all()->shouldReturn([
            'resize-image-jpeg'    => $resizeTransformation,
            'resize-image-tiff'    => $resizeTransformation
        ]);

        $this->shouldThrow(
            new AlreadyRegisteredTransformationException(
                'Transformation "resize" already registered for the mime type. "image/jpeg"')
        )->duringAdd($resizeTransformation);
    }
}
