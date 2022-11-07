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
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintValidator;

final class FingerprintValidator extends ConstraintValidator
{
    private const SHA512_CHECKSUM_REGEX = '/^[0-9A-Fa-f]{2}(:[0-9A-Fa-f]{2}){63}$/';
    private const MD5_CHECKSUM_REGEX = '/^[0-9A-Fa-f]{2}(:[0-9A-Fa-f]{2}){15}$/';

    public function validate($value, Constraint $constraint): void
    {
        $this->context->getValidator()->inContext($this->context)->validate(
            $value,
            new AtLeastOneOf(
                constraints: [
                    new Regex(self::SHA512_CHECKSUM_REGEX),
                    new Regex(self::MD5_CHECKSUM_REGEX),
                ],
                message: Fingerprint::INVALID_ENCODING,
                includeInternalMessages: false,
            ),
        );
    }
}
