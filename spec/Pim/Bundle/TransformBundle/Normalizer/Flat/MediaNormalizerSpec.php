<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;

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

    function it_supports_csv_normalization_of_media(ProductMediaInterface $media)
    {
        $this->supportsNormalization($media, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_media(ProductMediaInterface $media)
    {
        $this->supportsNormalization($media, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_media()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_media_by_using_the_export_path_by_default(
        $manager,
        ProductMediaInterface $media
    ) {
        $manager->getExportPath($media, null)->willReturn('files/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'front'])
            ->shouldReturn(['front' => 'files/foo.jpg']);
    }

    function it_normalizes_media_by_using_the_export_path(
        $manager,
        ProductMediaInterface $media
    ) {
        $manager->getExportPath($media, null)->willReturn('files/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'front', 'versioning' => false])
            ->shouldReturn(['front' => 'files/foo.jpg']);
    }

    function it_passes_custom_identifier_to_media_manager_if_given_in_the_context(
        $manager,
        ProductMediaInterface $media
    ) {
        $manager->getExportPath($media, 'foobar')->willReturn('files/foobar/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'front', 'identifier' => 'foobar'])
            ->shouldReturn(['front' => 'files/foobar/foo.jpg']);
    }

    function it_normalizes_media_by_keeping_the_media_filename(ProductMediaInterface $media)
    {
        $media->getFilename()->willReturn('foo.jpg');
        $this
            ->normalize($media, null, ['field_name' => 'front', 'versioning' => true])
            ->shouldReturn(['front' => 'foo.jpg']);
    }

    function it_normalizes_media_by_using_file_and_export_path_to_prepare_the_copy(ProductMediaInterface $media, $manager)
    {
        $manager->getExportPath($media, null)->willReturn('files/sku/attribute/foo.jpg');
        $manager->getFilePath($media)->willReturn('/tmp/file/foo.jpg');

        $this
            ->normalize($media, null, ['field_name' => 'media', 'prepare_copy' => true])
            ->shouldReturn(['filePath' => '/tmp/file/foo.jpg', 'exportPath' => 'files/sku/attribute/foo.jpg' ]);
    }

    function it_throws_exception_when_the_context_field_name_key_is_not_provided(ProductMediaInterface $media)
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('Missing required "field_name" context value, got "foo, bar"'))
            ->duringNormalize($media, null, ['foo' => true, 'bar' => true]);
    }
}
