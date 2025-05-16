<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UploadedFile extends Constraint
{
    public array $types;

    public string $unsupportedExtensionMessage = 'The extension of the file is invalid ({{ extension }}). Allowed extensions are {{ extensions }}.';
    public string $fileIsCorruptedMessage = 'The content of the file is corrupted.';

    public function getRequiredOptions()
    {
        return ['types'];
    }
}
