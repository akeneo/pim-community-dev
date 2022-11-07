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

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\Infrastructure\Validation\Storage;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp\Fingerprint;
use AkeneoTest\Platform\Acceptance\ImportExport\Infrastructure\Validation\AbstractValidationTest;

final class ValidateFingerprintTest extends AbstractValidationTest
{
    private const VALID_SHA512_FINGERPRINT = '6f:0a:fc:c7:59:32:0d:7f:78:1b:76:24:a9:51:a4:f9:c3:35:4b:7c:e6:0d:28:d4:cd:5e:5d:62:51:85:e4:93:60:f4:ae:70:a1:ac:ba:1c:92:c7:f4:4a:55:3b:7e:ac:c3:14:0f:4f:d2:b7:e7:87:d7:4f:e2:6d:1e:ab:0c:92';
    private const VALID_MD5_FINGERPRINT = '6f:0a:fc:c7:59:32:0d:7f:78:1b:76:24:a9:51:a4:f9';

    public function test_it_does_not_build_violation_when_fingerprint_is_valid(): void
    {
        $violations = $this->getValidator()->validate(self::VALID_SHA512_FINGERPRINT, new Fingerprint());

        $this->assertNoViolation($violations);

        $violations = $this->getValidator()->validate(self::VALID_MD5_FINGERPRINT, new Fingerprint());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violation_when_fingerprint_is_invalid(): void
    {
        $violations = $this->getValidator()->validate('foo', new Fingerprint());

        $this->assertHasValidationError(Fingerprint::INVALID_ENCODING, '', $violations);

        $violations = $this->getValidator()->validate('6f0afcc759320d7f781b7624a951a4f9', new Fingerprint());

        $this->assertHasValidationError(Fingerprint::INVALID_ENCODING, '', $violations);
    }
}
