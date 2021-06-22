<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Email as BaseEmail;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Email extends BaseEmail
{
    public $message = 'The %attribute% attribute requires an e-mail address.';

    public $attributeCode = '';
}
