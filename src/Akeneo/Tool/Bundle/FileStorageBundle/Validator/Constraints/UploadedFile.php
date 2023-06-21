<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UploadedFile extends Constraint
{
    public array $types;

    public string $unsupportedExtensionMessage = 'The extension of the file is invalid ({{ extension }}). Allowed extensions are {{ extensions }}.';
    public string $unsupportedMimeTypeMessage = 'The mime type of the file is invalid ({{ mimeType }}). Allowed mime types are {{ mimeTypes }}.';
    public string $invalidExtensionMessage = 'The extension of the file ({{ originalExtension }}) does not match its content.';
    public string $invalidMimeTypeMessage = 'The mime type of the file ({{ originalMimeType }}) does not match its content.';
    public string $mimeTypeDoesNotMatchExtensionMessage = 'The mime type of the file ({{ originalMimeType }}) does not match its extension ({{ originalExtension }}).';

    public function getRequiredOptions()
    {
        return ['types'];
    }
}
