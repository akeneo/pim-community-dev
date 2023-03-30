<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemorySimpleSelectNomenclatureRepositorySpec extends ObjectBehavior
{
    public function it_is_a_simple_select_nomenclature_repository(): void
    {
        $this->shouldImplement(SimpleSelectNomenclatureRepository::class);
    }

    public function it_can_save_simple_select_nomenclatures(): void
    {
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['s' => 'sma', 'm' => 'med']
        );

        $this->update('size', $simpleSelectNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'size' => $simpleSelectNomenclature,
        ]);

        $anotherSimpleSelectNomenclature = new NomenclatureDefinition(
            '<=',
            5,
            true,
            ['blue' => 'blue', 'red' => 'red']
        );

        $this->update('color', $anotherSimpleSelectNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'size' => $simpleSelectNomenclature,
            'color' => $anotherSimpleSelectNomenclature,
        ]);
    }

    public function it_can_update_simple_select_nomenclatures(): void
    {
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['s' => 'sma', 'm' => 'med']
        );

        $this->update('size', $simpleSelectNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'size' => $simpleSelectNomenclature,
        ]);

        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['m' => null, 'l' => 'lrg']
        );

        $this->update('siZE', $simpleSelectNomenclature);
        $this->nomenclatureDefinitions->shouldBeLike([
            'size' => new NomenclatureDefinition(
                '=',
                5,
                false,
                ['s' => 'sma', 'l' => 'lrg']
            ),
        ]);
    }
}
