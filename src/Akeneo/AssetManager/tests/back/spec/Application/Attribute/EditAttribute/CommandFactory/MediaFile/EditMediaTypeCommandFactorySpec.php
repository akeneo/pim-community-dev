<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaFile;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaFile\EditMediaTypeCommandFactory;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use PhpSpec\ObjectBehavior;

class EditMediaTypeCommandFactorySpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType(EditMediaTypeCommandFactory::class);
    }

    function it_supports_media_type_updates_on_media_file_attribute()
    {
        $this->supports([
            'media_type' => MediaType::IMAGE,
            'identifier' => 'dummy_identifier',
            'type'       => MediaFileAttribute::ATTRIBUTE_TYPE
        ])->shouldReturn(true);

        $this->supports([
            'media_type' => MediaType::IMAGE,
            'identifier' => 'dummy_identifier',
            'type'       => 'wrong_type'
        ])->shouldReturn(false);

        $this->supports([
            'media_type' => MediaType::IMAGE,
            'identifier' => 'dummy_identifier',
        ])->shouldReturn(false);
    }

    function it_creates_an_edit_media_type_command()
    {
        $command = $this->create([
            'media_type' => MediaType::IMAGE,
            'identifier' => 'dummy_identifier',
            'type'       => MediaFileAttribute::ATTRIBUTE_TYPE
        ]);

        $command->mediaType->shouldEqual(MediaType::IMAGE);
        $command->identifier->shouldEqual('dummy_identifier');
    }

    function it_throws_if_it_doesnt_support_the_normalized_command()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('create', [[
                'media_type' => MediaType::IMAGE,
                'identifier' => 'dummy_identifier',
                'type'       => 'wrong_type'
            ]]);
    }
}
