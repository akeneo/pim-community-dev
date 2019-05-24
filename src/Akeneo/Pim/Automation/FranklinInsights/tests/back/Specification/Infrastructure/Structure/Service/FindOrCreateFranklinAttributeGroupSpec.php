<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\FindOrCreateFranklinAttributeGroupInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroupCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\CreateAttribute;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\FindOrCreateFranklinAttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FindOrCreateFranklinAttributeGroupSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        AttributeGroupRepositoryInterface $repository,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($factory, $saver, $repository, $validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindOrCreateFranklinAttributeGroup::class);
    }

    public function it_is_a_find_or_create_franklin_attribute_group(): void
    {
        $this->shouldImplement(FindOrCreateFranklinAttributeGroupInterface::class);
    }

    public function it_finds_the_already_existing_attribute_group(
        $repository,
        AttributeGroupInterface $attributeGroup
    ): void {
        $repository->findOneByIdentifier((string) new FranklinAttributeGroupCode())->willReturn($attributeGroup);

        $this->findOrCreate()->shouldReturn($attributeGroup);
    }

    public function it_creates_the_franklin_attribute_group(
        $factory,
        $saver,
        $repository,
        $validator,
        AttributeGroupInterface $attributeGroup,
        ConstraintViolationListInterface $violations
    ): void {
        $attrGroupCode = new FranklinAttributeGroupCode();
        $repository->findOneByIdentifier((string) $attrGroupCode)->willReturn(null);

        $factory->create()->willReturn($attributeGroup);
        $attributeGroup->setCode((string) $attrGroupCode);

        $validator->validate($attributeGroup)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->save($attributeGroup)->shouldBeCalled();

        $this->findOrCreate()->shouldReturn($attributeGroup);
    }
}
