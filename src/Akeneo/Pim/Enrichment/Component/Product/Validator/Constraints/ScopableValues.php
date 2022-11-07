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
    const SCOPABLE_VALUES = '76b68325-0571-4de6-8094-f07d0a652bfa';

    public $unknownScopeMessage = 'The %attribute_code% attribute requires a value per channel. The %channel% channel (scope) must exist in your PIM';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_scopable_values_validator';
    }
}
