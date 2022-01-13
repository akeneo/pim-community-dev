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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindFamilyLabel;
use PhpSpec\ObjectBehavior;

class FindFamilyLabelSpec extends ObjectBehavior
{
    public function let(
        GetFamilyTranslations $getFamilyTranslations
    ): void {
        $this->beConstructedWith($getFamilyTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindFamilyLabel::class);
    }

    public function it_finds_the_label_of_a_family(
        GetFamilyTranslations $getFamilyTranslations
    ): void {
        $expectedLabel = 'Variant by Size';
        $familyCode = 'shoes';
        $localeCode = 'fr_FR';

        $getFamilyTranslations->byFamilyCodesAndLocale([$familyCode], $localeCode)
            ->willReturn([$familyCode => $expectedLabel]);

        $this->byCode($familyCode, $localeCode)->shouldReturn($expectedLabel);
    }

    public function it_returns_null_if_label_is_empty(
        GetFamilyTranslations $getFamilyTranslations
    ): void {
        $familyCode = 'by_size';
        $localeCode = 'fr_FR';

        $getFamilyTranslations->byFamilyCodesAndLocale([$familyCode], $localeCode)
            ->willReturn([]);

        $this->byCode($familyCode, $localeCode)->shouldReturn(null);
    }
}
