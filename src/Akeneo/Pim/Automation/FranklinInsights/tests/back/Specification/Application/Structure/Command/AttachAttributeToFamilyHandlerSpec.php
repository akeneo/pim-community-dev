<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\AttachAttributeToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AttachAttributeToFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository
    ): void
    {
        $this->beConstructedWith($updateFamily, $franklinAttributeAddedToFamilyRepository);
    }

    public function it_attaches_an_attribute_to_a_family(
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository,
        AttachAttributeToFamilyCommand $command
    ): void
    {
        $attributeCode = new AttributeCode('attribute_code');
        $familyCode = new FamilyCode('family_code');

        $command->getPimAttributeCode()->willReturn($attributeCode);
        $command->getPimFamilyCode()->willReturn($familyCode);

        $updateFamily
            ->addAttributeToFamily($attributeCode, $familyCode)
            ->shouldBeCalled();

        $franklinAttributeAddedToFamilyRepository
            ->save(Argument::type(FranklinAttributeAddedToFamily::class))
            ->shouldBeCalled();

        $this->handle($command->getWrappedObject())->shouldReturn(null);
    }
}
