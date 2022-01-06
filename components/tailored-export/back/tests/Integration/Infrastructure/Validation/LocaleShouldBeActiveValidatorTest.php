<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Akeneo\Test\Integration\Configuration;

class LocaleShouldBeActiveValidatorTest extends AbstractValidationTest
{
    public function test_it_validate_that_locale_is_active(): void
    {
        $violations = $this->getValidator()->validate('en_US', new LocaleShouldBeActive());

        $this->assertNoViolation($violations);
    }

    public function test_it_builds_violations_when_locale_is_not_active(): void
    {
        $violations = $this->getValidator()->validate('fr_FR', new LocaleShouldBeActive());

        $this->assertHasValidationError('akeneo.tailored_export.validation.locale.should_be_active', '', $violations);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
