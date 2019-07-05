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


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\AddAttributeToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AddAttributeToFamilyCommandSpec extends ObjectBehavior
{
    private $pimAttributeCode;

    private $pimFamilyCode;

    public function let(): void
    {
        $this->pimAttributeCode = new AttributeCode('attribute_code');
        $this->pimFamilyCode = new FamilyCode('family_code');

        $this->beConstructedWith($this->pimAttributeCode, $this->pimFamilyCode);
    }

    public function it_should_be_an_attach_attribute_to_family_command(): void
    {
        $this->shouldBeAnInstanceOf(AddAttributeToFamilyCommand::class);

        $this->getPimAttributeCode()->shouldBe($this->pimAttributeCode);
        $this->getPimFamilyCode()->shouldBe($this->pimFamilyCode);
    }
}
