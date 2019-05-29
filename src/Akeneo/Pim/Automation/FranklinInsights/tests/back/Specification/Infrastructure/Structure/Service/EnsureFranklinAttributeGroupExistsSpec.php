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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectActiveLocaleCodesManagedByFranklinQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\EnsureFranklinAttributeGroupExists;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EnsureFranklinAttributeGroupExistsSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $factory,
        SaverInterface $saver,
        AttributeGroupRepositoryInterface $repository,
        ValidatorInterface $validator,
        SelectActiveLocaleCodesManagedByFranklinQueryInterface $englishActiveLocaleCodesQuery
    ): void {
        $this->beConstructedWith($factory, $saver, $repository, $validator, $englishActiveLocaleCodesQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EnsureFranklinAttributeGroupExists::class);
    }

    public function it_is_a_find_or_create_franklin_attribute_group(): void
    {
        $this->shouldImplement(EnsureFranklinAttributeGroupExistsInterface::class);
    }

    public function it_finds_the_already_existing_attribute_group(
        $repository,
        $factory,
        $validator,
        $saver,
        AttributeGroupInterface $attributeGroup
    ): void {
        $repository->findOneByIdentifier(FranklinAttributeGroup::CODE)->willReturn($attributeGroup);
        $factory->create()->shouldNotBeCalled();
        $validator->validate($attributeGroup)->shouldNotBeCalled();
        $saver->save($attributeGroup)->shouldNotBeCalled();

        $this->ensureExistence();
    }

    public function it_creates_the_franklin_attribute_group(
        $factory,
        $saver,
        $repository,
        $validator,
        $englishActiveLocaleCodesQuery,
        AttributeGroupInterface $attributeGroup,
        ConstraintViolationListInterface $violations
    ): void {
        $repository->findOneByIdentifier(FranklinAttributeGroup::CODE)->willReturn(null);
        $englishActiveLocaleCodesQuery->execute()->willReturn([new LocaleCode('en_GB')]);

        $factory->create()->willReturn($attributeGroup);
        $attributeGroup->setCode(FranklinAttributeGroup::CODE);
        $attributeGroup->setLocale('en_GB');
        $attributeGroup->setLabel(FranklinAttributeGroup::LABEL);

        $validator->validate($attributeGroup)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->save($attributeGroup)->shouldBeCalled();

        $this->ensureExistence();
    }
}
