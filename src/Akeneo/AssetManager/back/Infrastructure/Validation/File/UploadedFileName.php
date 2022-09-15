<?php

namespace Akeneo\AssetManager\Infrastructure\Validation\File;

use Symfony\Component\Validator\Constraint;

class UploadedFileName extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.asset.validation.file.invalid_filename';
}
