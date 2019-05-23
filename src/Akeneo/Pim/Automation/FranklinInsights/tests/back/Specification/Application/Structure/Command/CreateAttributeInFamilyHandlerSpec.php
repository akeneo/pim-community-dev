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
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\UpdateFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyHandlerSpec extends ObjectBehavior
{

    public function let(CreateAttributeInterface $createAttribute, UpdateFamilyInterface $updateFamily)
    {
        $this->beConstructedWith($createAttribute, $updateFamily);
    }

    public function it_is_a_create_attribute_in_family_handler()
    {
        $this->shouldBeAnInstanceOf(CreateAttributeInFamilyHandler::class);
    }

    public function it_creates_an_attribute_and_adds_it_to_the_family($createAttribute, $updateFamily)
    {
        $pimAttrCode = AttributeCode::fromString('Franklin_attr_label');

        $createAttribute->create(
            $pimAttrCode,
            new AttributeLabel('Franklin attr label'),
            AttributeTypes::TEXT,
            'franklin'
        )->shouldBeCalled();

        $updateFamily->addAttributeToFamily($pimAttrCode, new FamilyCode('my_family_code'))->shouldBeCalled();

        $command= new CreateAttributeInFamilyCommand(
            new FamilyCode('my_family_code'),
            new FranklinAttributeLabel('Franklin attr label'),
            new FranklinAttributeType('text')
        );
        $this->handle($command)->shouldReturn(null);
    }
}
