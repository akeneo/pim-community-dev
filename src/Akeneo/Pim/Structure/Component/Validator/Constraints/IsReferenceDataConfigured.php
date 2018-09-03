<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if data is a reference data and if this reference data is configured.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsReferenceDataConfigured extends Constraint
{
    /** @var string */
    public $message = 'Reference data "%reference_data_name%" does not exist. Allowed values are: %references%';

    /** @var string */
    public $propertyPath = 'reference_data_name';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_is_reference_data_configured_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
