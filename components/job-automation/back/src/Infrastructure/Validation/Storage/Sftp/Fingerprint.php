<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp;

use Symfony\Component\Validator\Constraint;

final class Fingerprint extends Constraint
{
    public const INVALID_ENCODING = 'pim_import_export.form.job_instance.validation.fingerprint.invalid_encoding';
}
