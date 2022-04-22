<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class FilePath extends Constraint
{
    public const BAD_EXTENSION = 'akeneo.job_automation.file_path.bad_extension';
}
