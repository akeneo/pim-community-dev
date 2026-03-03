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
    const ATTRIBUTE_OPTION_DOES_NOT_EXIST = '7b82ed14-d9f9-41a1-b862-92d7c339d586';
    const ATTRIBUTE_OPTIONS_DO_NOT_EXIST = 'd0307701-0536-44fb-84a0-73c1b5879a83';

    public $message = 'The %invalid_option% value is not in the %attribute_code% attribute option list.';
    public $messagePlural = 'The %invalid_options% values are not in the %attribute_code% attribute option list.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'attribute_options_exist_validator';
    }
}
