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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the reference entity is well configured for attribute entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsReferenceEntityConfigured extends Constraint
{
    /** @var string */
    public $unknownMessage = 'The reference entity "%reference_entity_identifier%" does not exist.';

    /** @var string */
    public $invalidMessage = 'The reference entity "%reference_entity_identifier%" identifier is not valid';

    /** @var string */
    public $emptyMessage = 'You need to define a reference entity type for your attribute';

    /** @var string */
    public $propertyPath = 'reference_data_name';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_is_reference_entity_configured_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
