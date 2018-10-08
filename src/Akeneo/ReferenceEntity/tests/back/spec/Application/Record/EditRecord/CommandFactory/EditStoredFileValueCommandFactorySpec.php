<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditStoredFileValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

class EditStoredFileValueCommandFactorySpec extends ObjectBehavior
{
    public function it_supports_an_attribute_and_a_value(
        ImageAttribute $imageAttribute,
        TextAttribute $textAttribute
    ) {
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(true);

        // Missing data key
        $normalizedValue = [
            '' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Not data array
        $normalizedValue = [
            'data' => 650
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Extra fields
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
                'other' => 'test',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Missing filepath
        $normalizedValue = [
            'data' => [
                '' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Missing originalFilename
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                '' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Missing size
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                '' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Missing mimeType
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                '' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Missing extension
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                '' => 'png',
            ]
        ];
        $this->supports($imageAttribute, $normalizedValue)->shouldReturn(false);

        // Wrong attribute
        $normalizedValue = [
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];
        $this->supports($textAttribute, $normalizedValue)->shouldReturn(false);
    }

    public function it_creates_an_edit_stored_file_value_command_from_an_attribute_and_a_value(
        ImageAttribute $imageAttribute
    ) {
        $normalizedValue = [
            'attribute' => [],
            'channel' => 'ecommerce',
            'locale' => 'de_DE',
            'data' => [
                'filePath' => '/tmp/jambon.png',
                'originalFilename' => 'jambon.png',
                'size' => 1024,
                'mimeType' => 'image/png',
                'extension' => 'png',
            ]
        ];

        $expectedCommand = new EditStoredFileValueCommand();
        $expectedCommand->attribute = $imageAttribute->getWrappedObject();
        $expectedCommand->channel = 'ecommerce';
        $expectedCommand->locale = 'de_DE';
        $expectedCommand->filePath = '/tmp/jambon.png';
        $expectedCommand->originalFilename = 'jambon.png';
        $expectedCommand->size = 1024;
        $expectedCommand->mimeType = 'image/png';
        $expectedCommand->extension = 'png';

        $this->create($imageAttribute, $normalizedValue)->shouldBeLike($expectedCommand);
    }
}
