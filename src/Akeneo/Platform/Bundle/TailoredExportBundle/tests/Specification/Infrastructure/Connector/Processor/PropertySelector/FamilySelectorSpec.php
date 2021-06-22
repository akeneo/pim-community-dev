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

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use PhpSpec\ObjectBehavior;

class FamilySelectorSpec extends ObjectBehavior
{
    public function let(
        GetFamilyTranslations $getFamilyTranslations
    ) {
        $this->beConstructedWith($getFamilyTranslations);
    }

    public function it_returns_property_name_supported()
    {
        $this->supports(['type' => 'code'], 'family')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'categories')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'family')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'family')->shouldReturn(false);
    }

    public function it_selects_the_code(
        EntityWithFamilyInterface $entity,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('accessories');
        $entity->getFamily()->willReturn($family);

        $this->applySelection(['type' => 'code'], $entity)->shouldReturn('accessories');
    }

    public function it_selects_the_label(
        $getFamilyTranslations,
        EntityWithFamilyInterface $entity,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('accessories');
        $entity->getFamily()->willReturn($family);
        $getFamilyTranslations->byFamilyCodesAndLocale(['accessories'], 'fr_FR')
            ->willReturn(['accessories' => 'Accessoires']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('Accessoires');
    }

    public function it_fallbacks_on_the_code_when_not_translated(
        $getFamilyTranslations,
        EntityWithFamilyInterface $entity,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('scanners');
        $entity->getFamily()->willReturn($family);
        $getFamilyTranslations->byFamilyCodesAndLocale(['scanners'], 'fr_FR')
            ->willReturn([]);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('[scanners]');
    }

    public function it_returns_empty_string_when_entity_has_no_family(
        EntityWithFamilyInterface $entity
    ) {
        $entity->getFamily()->willReturn(null);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
        ], $entity)->shouldReturn('');
    }
}
