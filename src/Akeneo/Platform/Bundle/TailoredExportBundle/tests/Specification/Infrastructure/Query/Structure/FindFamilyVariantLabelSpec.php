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

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure\FindFamilyVariantLabel;
use PhpSpec\ObjectBehavior;

class FindFamilyVariantLabelSpec extends ObjectBehavior
{
    public function let(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ): void {
        $this->beConstructedWith($getFamilyVariantTranslations);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(FindFamilyVariantLabel::class);
    }

    public function it_finds_the_label_of_a_family_variant(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ): void {
        $expectedLabel = 'Variant by Size';
        $familyVariantCode = 'by_size';
        $localeCode = 'fr_FR';

        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale([$familyVariantCode], $localeCode)
            ->willReturn([$familyVariantCode => $expectedLabel]);

        $this->byCode($familyVariantCode, $localeCode)->shouldReturn($expectedLabel);
    }

    public function it_returns_null_if_label_is_empty(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ): void {
        $familyVariantCode = 'by_size';
        $localeCode = 'fr_FR';

        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale([$familyVariantCode], $localeCode)
            ->willReturn([]);

        $this->byCode($familyVariantCode, $localeCode)->shouldReturn(null);
    }
}
