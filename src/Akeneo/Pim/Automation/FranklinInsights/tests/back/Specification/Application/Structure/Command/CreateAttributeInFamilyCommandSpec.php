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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyCommandSpec extends ObjectBehavior
{
    private $pimFamilyCode;
    private $franklinAttributeLabel;
    private $franklinAttributeType;
    private $pimAttributeCode;

    public function let(): void
    {
        $this->pimFamilyCode = new FamilyCode('my_family_code');
        $this->franklinAttributeLabel = new FranklinAttributeLabel('Franklin attribute label');
        $this->franklinAttributeType = new FranklinAttributeType('text');
        $this->pimAttributeCode = AttributeCode::fromString((string) $this->franklinAttributeLabel);

        $this->beConstructedWith(
            $this->pimFamilyCode,
            $this->pimAttributeCode,
            $this->franklinAttributeLabel,
            $this->franklinAttributeType
        );
    }

    public function it_is_a_create_attribute_in_family_command(): void
    {
        $this->shouldBeAnInstanceOf(CreateAttributeInFamilyCommand::class);
    }

    public function it_returns_the_pim_family_code(): void
    {
        $this->getPimFamilyCode()->shouldReturn($this->pimFamilyCode);
    }

    public function it_returns_franklin_attribute_label(): void
    {
        $this->getFranklinAttributeLabel()->shouldReturn($this->franklinAttributeLabel);
    }

    public function it_returns_franklin_attribute_type(): void
    {
        $this->getFranklinAttributeType()->shouldReturn($this->franklinAttributeType);
    }

    public function it_returns_pim_attribute_code(): void
    {
        $this->getPimAttributeCode()->shouldReturn($this->pimAttributeCode);
    }
}
