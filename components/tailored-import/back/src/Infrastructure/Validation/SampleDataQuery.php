<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SampleDataQuery extends Constraint
{
    const MISSING_PROPERTY = "akeneo.tailored_import.validation.sample_data.get_sample_data_query";
}
