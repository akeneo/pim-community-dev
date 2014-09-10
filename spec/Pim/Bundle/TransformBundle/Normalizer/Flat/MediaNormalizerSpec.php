<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

class MediaNormalizerSpec extends ObjectBehavior
{
    function let(MediaManager $manager)
    {
        $this->beConstructedWith($manager);
    }

    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_csv_normalization_of_media(AbstractProductMedia $media)
    {
        $this->supportsNormalization($media, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_media(AbstractProductMedia $media)
    {
        $this->supportsNormalization($media, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_media()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_media_by_using_the_export_path_by_default(
        $manager,
        AbstractProductMedia $media
    ) {
        $manager->getExportPath($media)->willReturn('files/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'front'])
            ->shouldReturn(['front' => 'files/foo.jpg']);
    }

    function it_normalizes_media_by_using_the_export_path(
        $manager,
        AbstractProductMedia $media
    ) {
        $manager->getExportPath($media)->willReturn('files/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'front', 'versioning' => false])
            ->shouldReturn(['front' => 'files/foo.jpg']);
    }

    function it_normalizes_media_by_keeping_the_media_filename(AbstractProductMedia $media)
    {
        $media->getFilename()->willReturn('foo.jpg');
        $this
            ->normalize($media, null, ['field_name' => 'front', 'versioning' => true])
            ->shouldReturn(['front' => 'foo.jpg']);
    }

    function it_throws_exception_when_the_context_field_name_key_is_not_provided(AbstractProductMedia $media)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('Missing required "field_name" context value, got "foo, bar"'))
            ->duringNormalize($media, null, ['foo' => true, 'bar' => true]);
    }
}
