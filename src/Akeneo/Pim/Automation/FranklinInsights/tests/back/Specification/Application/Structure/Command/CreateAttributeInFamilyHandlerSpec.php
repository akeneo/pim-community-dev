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
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\AddAttributeToFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        CreateAttributeInterface $createAttribute,
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        AddAttributeToFamilyHandler $addAttributeToFamilyHandler
    ) {
        $this->beConstructedWith(
            $createAttribute,
            $attributeCreatedRepository,
            $addAttributeToFamilyHandler
        );
    }

    public function it_is_a_create_attribute_in_family_handler()
    {
        $this->shouldBeAnInstanceOf(CreateAttributeInFamilyHandler::class);
    }

    public function it_creates_an_attribute_and_adds_it_to_the_family(
        $createAttribute,
        $attributeCreatedRepository,
        $addAttributeToFamilyHandler
    ) {
        $pimAttrCode = AttributeCode::fromLabel('Franklin attr label');
        $pimFamilyCode = new FamilyCode('my_family_code');
        $franklinAttrLabel = new FranklinAttributeLabel('Franklin attr label');
        $franklinAttrType = new FranklinAttributeType('text');

        $createAttribute->create(new Attribute(
            $pimAttrCode,
            new AttributeLabel('Franklin attr label'),
            new AttributeType(AttributeTypes::TEXT)
        ))->shouldBeCalled();

        $attributeCreatedRepository->save(Argument::type(FranklinAttributeCreated::class));

        $addAttributeToFamilyHandler
            ->handle(Argument::type(AddAttributeToFamilyCommand::class))
            ->shouldBeCalled();

        $command = new CreateAttributeInFamilyCommand(
            $pimFamilyCode,
            $pimAttrCode,
            $franklinAttrLabel,
            $franklinAttrType
        );
        $this->handle($command)->shouldReturn(null);
    }

    public function it_creates_a_text_attribute_for_a_metric_attribute(
        $createAttribute,
        $attributeCreatedRepository,
        $addAttributeToFamilyHandler
    ) {
        $pimAttrCode = AttributeCode::fromLabel('Franklin metric attribute label');
        $pimFamilyCode = new FamilyCode('my_family_code');
        $franklinAttrLabel = new FranklinAttributeLabel('Franklin metric attribute label');
        $franklinAttrType = new FranklinAttributeType('metric');

        $createAttribute->create(new Attribute(
            $pimAttrCode,
            new AttributeLabel('Franklin metric attribute label'),
            new AttributeType(AttributeTypes::TEXT)
        ))->shouldBeCalled();

        $attributeCreatedRepository->save(Argument::type(FranklinAttributeCreated::class));

        $addAttributeToFamilyHandler
            ->handle(Argument::type(AddAttributeToFamilyCommand::class))
            ->shouldBeCalled();

        $command = new CreateAttributeInFamilyCommand(
            $pimFamilyCode,
            $pimAttrCode,
            $franklinAttrLabel,
            $franklinAttrType
        );
        $this->handle($command)->shouldReturn(null);
    }
}
