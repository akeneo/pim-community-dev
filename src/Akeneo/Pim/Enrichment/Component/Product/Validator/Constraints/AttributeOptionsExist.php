<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsExist extends Constraint
{
    public $message = 'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist';
    public $messagePlural = 'Property "%attribute_code%" expects valid codes. The following options do not exist: "%invalid_options%"';

    /**
     * {@inheritdoc}}
     */
    public function validatedBy()
    {
        return 'attribute_options_exist_validator';
    }
}
