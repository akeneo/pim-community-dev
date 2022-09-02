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

namespace Akeneo\Test\Pim\TableAttribute\Helper;

use Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle;
use PhpSpec\Exception\Example\SkippingException;
use PHPUnit\Framework\Assert;

final class FeatureHelper
{
    public static function skipSpecTestWhenReferenceEntityIsNotActivated(): void
    {
        if (!\class_exists(AkeneoReferenceEntityBundle::class)) {
            throw new SkippingException('ReferenceEntity are not available in this scope');
        }
    }

    public static function skipIntegrationTestWhenReferenceEntityIsNotActivated(): void
    {
        if (!\class_exists(AkeneoReferenceEntityBundle::class)) {
            Assert::markTestSkipped('ReferenceEntity are not available in this scope');
        }
    }
}
