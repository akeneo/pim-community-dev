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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkAddAttributesToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkAddAttributesToFamilyCommandSpec extends ObjectBehavior
{
    private $pimFamilyCode;
    private $attributesCodes;

    public function let(): void
    {
        $this->pimFamilyCode = new FamilyCode('my_family_code');
        $this->attributesCodes = [new AttributeCode('color'), new AttributeCode('height')];

        $this->beConstructedWith(
            $this->pimFamilyCode,
            $this->attributesCodes
        );
    }

    public function it_is_a_bulk_add_attributes_to_family_command(): void
    {
        $this->shouldBeAnInstanceOf(BulkAddAttributesToFamilyCommand::class);
    }

    public function it_returns_the_pim_family_code(): void
    {
        $this->getFamilyCode()->shouldReturn($this->pimFamilyCode);
    }

    public function it_returns_attribute_codes(): void
    {
        $this->getAttributeCodes()->shouldReturn($this->attributesCodes);
    }
}
