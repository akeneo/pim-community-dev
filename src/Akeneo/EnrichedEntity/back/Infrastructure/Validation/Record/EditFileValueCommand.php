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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditFileValueCommand extends Constraint
{
    public const FILE_EXTENSION_NOT_ALLOWED_MESSAGE = 'pim_enriched_entity.record.validation.file.extension_not_allowed';
    public const FILE_SIZE_EXCEEDED_MESSAGE = 'pim_enriched_entity.record.validation.file.file_size_exceeded';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
