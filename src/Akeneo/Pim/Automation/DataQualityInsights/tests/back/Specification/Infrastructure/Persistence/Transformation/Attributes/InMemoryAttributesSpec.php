<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributesSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'sku' => 1,
            'name' => 2,
            "description" => 42,
        ]);
    }

    public function it_gets_attributes_ids_from_codes(): void
    {
        $this->getIdsByCodes(['name', 'description'])->shouldReturn(['name' => 2, 'description' => 42]);
    }

    public function it_gets_attributes_codes_from_ids(): void
    {
        $this->getCodesByIds([2, 42])->shouldReturn([2 => 'name', 42 => 'description']);
    }

    public function it_ignores_unknown_attributes(): void
    {
        $this->getIdsByCodes(['name', 'title' ,'description'])->shouldReturn(['name' => 2, 'description' => 42]);
        $this->getCodesByIds([567, 2, 42])->shouldReturn([2 => 'name', 42 => 'description']);
    }
}
