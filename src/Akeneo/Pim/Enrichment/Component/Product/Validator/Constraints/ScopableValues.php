<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValues extends Constraint
{
    public $unknownScopeMessage = 'Attribute "%attribute_code%" expects an existing scope, "%channel%" given.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_scopable_values_validator';
    }
}
