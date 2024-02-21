<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Date as BaseDate;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Date extends BaseDate
{
    /** @var string */
    public $message = 'The %attribute% attribute requires a valid date. Please use the following format %date_format% for dates.';

    public string $attributeCode = '';
}
