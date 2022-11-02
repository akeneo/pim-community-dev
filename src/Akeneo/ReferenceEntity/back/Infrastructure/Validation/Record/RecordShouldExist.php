<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RecordShouldExist extends Constraint
{
    public const ERROR_MESSAGE = 'pim_reference_entity.record.validation.code.should_exist';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_referenceentity.validator.record.record_should_exist';
    }
}
