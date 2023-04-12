<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryReferenceEntityNomenclatureRepositorySpec extends ObjectBehavior
{
    public function it_is_a_reference_entity_nomenclature_repository(): void
    {
        $this->shouldImplement(ReferenceEntityNomenclatureRepository::class);
    }

    public function it_can_save_reference_entity_nomenclatures(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );

        $this->update('brand', $refEntityNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'brand' => $refEntityNomenclature,
        ]);

        $anotherRefEntityNomenclature = new NomenclatureDefinition(
            '<=',
            5,
            true,
            ['purple' => 'ppl', 'blue' => 'ble']
        );

        $this->update('color', $anotherRefEntityNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'brand' => $refEntityNomenclature,
            'color' => $anotherRefEntityNomenclature,
        ]);
    }

    public function it_can_update_reference_entity_nomenclatures_while_ignoring_case(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );

        $this->update('brand', $refEntityNomenclature);

        $this->nomenclatureDefinitions->shouldBeLike([
            'brand' => $refEntityNomenclature,
        ]);

        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['akeneo' => null, 'zara' => 'zra']
        );

        $this->update('braND', $refEntityNomenclature);
        $this->nomenclatureDefinitions->shouldBeLike([
            'brand' => new NomenclatureDefinition(
                '=',
                5,
                false,
                ['adidas' => 'adds', 'zara' => 'zra']
            ),
        ]);
    }

    public function it_can_retrieve_a_nomenclature_with_its_code_while_ignoring_case(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );

        $this->update('brand', $refEntityNomenclature);

        $result = $this->get('brand');
        $result->shouldBeLike($refEntityNomenclature);

        $resultWithCase = $this->get('brAND');
        $resultWithCase->shouldBeLike($refEntityNomenclature);
    }
}
