<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditStoredFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\File\FindFileDataByFileKeyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditStoredFileValueCommandFactorySpec extends ObjectBehavior
{
    function let(FindFileDataByFileKeyInterface $findFileData)
    {
        $this->beConstructedWith($findFileData);
    }

    function it_only_supports_image_attribute(TextAttribute $textAttribute)
    {
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/stark_portrait.png',
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($textAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_supports_value_with_file_path_as_data_property(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/stark_portrait.png',
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(true);
    }

    function it_supports_value_with_file_path_as_data(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'data' => '/tmp/stark_portrait.png',
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(true);
    }

    function it_does_not_support_value_without_data(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            '' => [
                'filePath' => '/tmp/stark_portrait.png',
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_does_not_support_a_value_with_null_as_file_path(ImageAttribute $imageAttribute) {
        $normalizedValue = [
            'data' => [
                'filePath' => null,
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_does_not_support_a_value_with_an_empty_string_as_file_path(ImageAttribute $imageAttribute) {
        $normalizedValue = [
            'data' => [
                'filePath' => '',
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_does_not_support_value_with_null_as_data(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'data' => null,
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_does_not_support_value_with_an_empty_string_as_data(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'data' => '',
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_does_not_support_value_without_file_path_in_data(ImageAttribute $imageAttribute)
    {
        $normalizedValue = [
            'data' => [
                '' => '/tmp/stark_portrait.png',
                'originalFilename' => 'stark_portrait.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);
    }

    function it_creates_an_edit_stored_file_value_command_from_an_attribute_and_a_complex_value(
        ImageAttribute $imageAttribute,
        FindFileDataByFileKeyInterface $findFileData
    ) {
        $normalizedValue = [
            'attribute' => [],
            'channel' => 'ecommerce',
            'locale' => 'de_DE',
            'data' => [
                'filePath' => '/tmp/stark_portrait.png',
                'originalFilename' => '',
                'size' => 42,
                'mimeType' => '',
                'extension' => '',
                'updatedAt' => '2019-11-22T15:16:21+0000',
            ]
        ];

        $findFileData->find('/tmp/stark_portrait.png')->willReturn([
            'filePath' => '/tmp/stark_portrait.png',
            'originalFilename' => 'stark_portrait.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ]);

        $expectedCommand = new EditStoredFileValueCommand(
            $imageAttribute->getWrappedObject(),
            'ecommerce',
            'de_DE',
            '/tmp/stark_portrait.png',
            'stark_portrait.png',
            1024,
            'image/png',
            'png',
            '2019-11-22T15:16:21+0000'
        );

        $this->create($imageAttribute, $normalizedValue)->shouldBeLike($expectedCommand);
    }

    function it_creates_an_edit_stored_file_value_command_from_an_attribute_and_a_simple_value(
        ImageAttribute $imageAttribute,
        FindFileDataByFileKeyInterface $findFileData
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale' => 'de_DE',
            'data' => '/tmp/stark_portrait.png'
        ];

        $findFileData->find('/tmp/stark_portrait.png')->willReturn([
            'filePath' => '/tmp/stark_portrait.png',
            'originalFilename' => 'stark_portrait.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ]);

        $expectedCommand = new EditStoredFileValueCommand(
            $imageAttribute->getWrappedObject(),
            'ecommerce',
            'de_DE',
            '/tmp/stark_portrait.png',
            'stark_portrait.png',
            1024,
            'image/png',
            'png',
            '2019-11-22T15:16:21+0000'
        );

        $this->create($imageAttribute, $normalizedValue)->shouldBeLike($expectedCommand);
    }

    function it_sets_the_updated_at_to_the_current_datetime_if_it_does_not_exist(
        ImageAttribute $imageAttribute,
        FindFileDataByFileKeyInterface $findFileData
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale' => 'de_DE',
            'data' => '/tmp/stark_portrait.png'
        ];

        $findFileData->find('/tmp/stark_portrait.png')->willReturn([
            'filePath' => '/tmp/stark_portrait.png',
            'originalFilename' => 'stark_portrait.png',
            'size' => 1024,
            'mimeType' => 'image/png',
            'extension' => 'png',
        ]);

        $this->create($imageAttribute, $normalizedValue)->updatedAt->shouldBeString();
    }
}
