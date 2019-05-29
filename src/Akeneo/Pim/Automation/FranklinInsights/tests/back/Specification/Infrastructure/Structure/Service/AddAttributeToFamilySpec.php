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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\AddAttributeToFamily;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AddAttributeToFamilySpec extends ObjectBehavior
{
    public function let(
        FamilyUpdater $updater,
        FamilySaver $saver,
        FamilyRepository $repository,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($updater, $saver, $repository, $validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AddAttributeToFamily::class);
    }

    public function it_is_an_implementation_of_add_attribute_to_family(): void
    {
        $this->shouldImplement(AddAttributeToFamilyInterface::class);
    }

    public function it_adds_attribute_to_a_family_without_attributes(
        $updater,
        $saver,
        $repository,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violations
    ): void {
        $repository->findOneByIdentifier('bar')->willReturn($family);
        $family->getAttributeCodes()->willReturn([]);

        $updater->update($family, ['attributes' => ['Foo']])->shouldBeCalled();
        $validator->validate($family)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);

        $saver->save($family)->shouldBeCalled();

        $this->addAttributeToFamily(
            AttributeCode::fromLabel('Foo'),
            new FamilyCode('bar')
        )->shouldReturn(null);
    }

    public function it_adds_attribute_to_a_family_with_attributes(
        $updater,
        $saver,
        $repository,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violations
    ): void {
        $repository->findOneByIdentifier('bar')->willReturn($family);
        $family->getAttributeCodes()->willReturn(['baz']);

        $updater->update($family, ['attributes' => ['baz', 'Foo']])->shouldBeCalled();
        $validator->validate($family)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);

        $saver->save($family)->shouldBeCalled();

        $this->addAttributeToFamily(
            AttributeCode::fromLabel('Foo'),
            new FamilyCode('bar')
        )->shouldReturn(null);
    }

    public function it_throws_an_exception_when_family_does_not_exist($repository): void
    {
        $repository->findOneByIdentifier('bar')->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'addAttributeToFamily',
                [
                    AttributeCode::fromLabel('Foo'),
                    new FamilyCode('bar')
                ]
            );
    }

    public function it_throws_an_exception_when_there_are_some_violations(
        $updater,
        $saver,
        $repository,
        $validator,
        FamilyInterface $family,
        ConstraintViolationListInterface $violations
    ): void {
        $repository->findOneByIdentifier('bar')->willReturn($family);
        $family->getAttributeCodes()->willReturn([]);

        $updater->update($family, ['attributes' => ['Foo']])->shouldBeCalled();
        $validator->validate($family)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(1);

        $saver->save($family)->shouldNotBeCalled();

        $this
            ->shouldThrow(new ViolationHttpException($violations->getWrappedObject()))
            ->during(
                'addAttributeToFamily',
                [
                    AttributeCode::fromLabel('Foo'),
                    new FamilyCode('bar')
                ]
            );
    }
}
