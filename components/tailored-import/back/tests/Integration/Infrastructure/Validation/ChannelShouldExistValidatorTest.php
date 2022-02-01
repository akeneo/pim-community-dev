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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Test\Integration\Configuration;

class ChannelShouldExistValidatorTest extends AbstractValidationTest
{
    public function test_it_validate_that_channel_exist(): void
    {
        $violations = $this->getValidator()->validate('ecommerce', new ChannelShouldExist());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violations_when_channel_does_not_exist(): void
    {
        $violations = $this->getValidator()->validate('unknown_channel', new ChannelShouldExist());

        $this->assertHasValidationError('akeneo.tailored_import.validation.channel.should_exist', '', $violations);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
