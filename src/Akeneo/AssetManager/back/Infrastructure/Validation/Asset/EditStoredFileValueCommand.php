<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditStoredFileValueCommand extends Constraint
{
    public const FILE_EXTENSION_NOT_ALLOWED_MESSAGE = 'pim_reference_entity.record.validation.file.extension_not_allowed';
    public const FILE_SIZE_EXCEEDED_MESSAGE = 'pim_reference_entity.record.validation.file.file_size_exceeded';
    public const FILE_SHOULD_EXIST = 'pim_reference_entity.record.validation.file.should_exist';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_referenceentity.validator.record.edit_stored_file_value_command';
    }
}
