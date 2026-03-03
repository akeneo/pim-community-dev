<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryFamilyNomenclatureRepository;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFamilyNomenclatureRepositorySpec extends ObjectBehavior
{
    public function it_is_a_family_nomenclature_repository(): void
    {
        $this->shouldImplement(InMemoryFamilyNomenclatureRepository::class);
    }

    public function it_can_save_family_nomenclatures(): void
    {
        $familyNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['family1' => 'Foo', 'family2' => 'Bar']
        );

        $this->update($familyNomenclature);

        $result = $this->get();
        $result->shouldBeLike($familyNomenclature);
    }

    public function it_can_update_simple_select_nomenclatures_while_ignoring_case(): void
    {
        $valuesWithKeysUsingCase = ['fAmIlY1' => 'Foo', 'FaMiLy2' => 'Bar'];
        $familyNomenclatureWithCase = new NomenclatureDefinition(
            '=',
            3,
            false,
            $valuesWithKeysUsingCase
        );

        $this->update($familyNomenclatureWithCase);

        $valuesWithKeysInLowerCase = ['family1' => 'Foo', 'family2' => 'Bar'];
        $familyNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            $valuesWithKeysInLowerCase
        );

        $result = $this->get();
        $result->shouldBeLike($familyNomenclature);
    }
}
