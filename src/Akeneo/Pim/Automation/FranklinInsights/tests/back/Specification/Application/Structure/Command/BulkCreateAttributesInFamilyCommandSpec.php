<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkCreateAttributesInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToCreate;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkCreateAttributesInFamilyCommandSpec extends ObjectBehavior
{
    private $pimFamilyCode;
    private $attributesToCreate;

    public function let(): void
    {
        $this->pimFamilyCode = new FamilyCode('my_family_code');
        $this->attributesToCreate = new AttributesToCreate([
            [
                'franklinAttributeLabel' => 'color',
                'franklinAttributeType' => 'text',
            ],
            [
                'franklinAttributeLabel' => 'height',
                'franklinAttributeType' => 'number',
            ],
        ]);

        $this->beConstructedWith(
            $this->pimFamilyCode,
            $this->attributesToCreate
        );
    }

    public function it_is_a_bulk_create_attribute_in_family_command(): void
    {
        $this->shouldBeAnInstanceOf(BulkCreateAttributesInFamilyCommand::class);
    }

    public function it_returns_the_pim_family_code(): void
    {
        $this->getPimFamilyCode()->shouldReturn($this->pimFamilyCode);
    }

    public function it_returns_attributes_to_create(): void
    {
        $this->getAttributesToCreate()->shouldReturn($this->attributesToCreate);
    }
}
