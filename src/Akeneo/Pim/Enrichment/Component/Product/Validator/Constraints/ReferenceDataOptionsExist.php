<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataOptionsExist extends Constraint
{
    public $message = 'Property "%attribute_code%" expects a valid reference data code. The code "%invalid_code%" of the reference data "%reference_data_name%" does not exist';
    public $messagePlural = 'Property "%attribute_code%" expects valid codes. The following codes for reference data "%reference_data_name%" do not exist: "%invalid_codes%"';

    /**
     * {@inheritdoc}}
     */
    public function validatedBy()
    {
        return 'reference_data_options_exist_validator';
    }
}
