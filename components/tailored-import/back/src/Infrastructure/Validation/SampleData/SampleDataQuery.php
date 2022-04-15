<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SampleDataQuery extends Constraint
{
    const MISSING_QUERY_PARAMS = "akeneo.tailored_import.validation.missing_query_params";
}
