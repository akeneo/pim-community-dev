<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PayloadFormat extends Constraint
{
    public string $message = 'Property "values" expects an array as data, "%s" given.';
}
