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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\EnrichedEntity;

use Symfony\Component\Validator\Constraint;

/**
 * Checks whether a given enriched entity identifier already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityIdentifierShouldBeUnique extends Constraint
{
    public const ERROR_MESSAGE = 'pim_enriched_entity.enriched_entity.validation.identifier.should_be_unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_enrichedentity.validator.enriched_entity.identifier_is_unique';
    }
}
