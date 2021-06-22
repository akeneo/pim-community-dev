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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use PhpSpec\ObjectBehavior;

class FamilyVariantSelectorSpec extends ObjectBehavior
{
    public function let(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ) {
        $this->beConstructedWith($getFamilyVariantTranslations);
    }

    public function it_returns_property_name_supported()
    {
        $this->supports(['type' => 'code'], 'family_variant')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'family')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'family_variant')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'family_variant')->shouldReturn(false);
    }

    public function it_selects_the_code(
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getCode()->willReturn('accessories');
        $entity->getFamilyVariant()->willReturn($familyVariant);

        $this->applySelection(['type' => 'code'], $entity)->shouldReturn('accessories');
    }

    public function it_selects_the_label(
        GetFamilyVariantTranslations $getFamilyVariantTranslations,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getCode()->willReturn('accessories');
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale(['accessories'], 'fr_FR')
            ->willReturn(['accessories' => 'Accessoires']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('Accessoires');
    }

    public function it_fallbacks_on_the_code_when_not_translated(
        GetFamilyVariantTranslations $getFamilyVariantTranslations,
        EntityWithFamilyVariantInterface $entity,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariant->getCode()->willReturn('scanners');
        $entity->getFamilyVariant()->willReturn($familyVariant);
        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale(['scanners'], 'fr_FR')
            ->willReturn([]);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('[scanners]');
    }

    public function it_returns_empty_string_when_entity_has_no_family_variant(
        EntityWithFamilyVariantInterface $entity
    ) {
        $entity->getFamilyVariant()->willReturn(null);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('');
    }
}
