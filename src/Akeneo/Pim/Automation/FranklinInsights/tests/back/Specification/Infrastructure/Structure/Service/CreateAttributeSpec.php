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
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectActiveLocaleCodesManagedByFranklinQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\CreateAttribute;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeSpec extends ObjectBehavior
{
    public function let(
        AttributeFactory $factory,
        AttributeUpdater $updater,
        AttributeSaver $saver,
        ValidatorInterface $validator,
        EnsureFranklinAttributeGroupExistsInterface $ensureFranklinAttributeGroupExists,
        SelectActiveLocaleCodesManagedByFranklinQueryInterface $activeLocaleCodesQuery
    ): void {
        $activeLocaleCodesQuery->execute()->willReturn([new LocaleCode('en_US')]);

        $this->beConstructedWith(
            $factory,
            $updater,
            $saver,
            $validator,
            $ensureFranklinAttributeGroupExists,
            $activeLocaleCodesQuery
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateAttribute::class);
    }

    public function it_is_a_create_attribute(): void
    {
        $this->shouldImplement(CreateAttributeInterface::class);
    }

    public function it_creates_an_attribute(
        $factory,
        $updater,
        $validator,
        $saver,
        $ensureFranklinAttributeGroupExists,
        AttributeInterface $attribute,
        ConstraintViolationListInterface $violations
    ): void {
        $attributeData = [
            'code' => 'Foo_bar',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $ensureFranklinAttributeGroupExists->ensureExistence()->shouldBeCalled();

        $factory->createAttribute('pim_catalog_text')->willReturn($attribute);
        $updater->update($attribute, $attributeData)->shouldBeCalled();
        $validator->validate($attribute)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->save($attribute)->shouldBeCalled();

        $this->create(
            AttributeCode::fromLabel('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_text')
        )->shouldReturn(null);
    }

    public function it_creates_an_attribute_number(
        $factory,
        $updater,
        $validator,
        $saver,
        $ensureFranklinAttributeGroupExists,
        AttributeInterface $attribute,
        ConstraintViolationListInterface $violations
    ): void {
        $attributeData = [
            'code' => 'Foo_bar',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false,
            "decimals_allowed" => true,
            "negative_allowed" => true
        ];

        $ensureFranklinAttributeGroupExists->ensureExistence()->shouldBeCalled();

        $factory->createAttribute('pim_catalog_number')->willReturn($attribute);
        $updater->update($attribute, $attributeData)->shouldBeCalled();
        $validator->validate($attribute)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->save($attribute)->shouldBeCalled();

        $this->create(
            AttributeCode::fromLabel('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_number')
        )->shouldReturn(null);
    }

    public function it_throws_an_exception_when_there_are_some_violations(
        $factory,
        $updater,
        $validator,
        $saver,
        $ensureFranklinAttributeGroupExists,
        AttributeInterface $attribute,
        ConstraintViolationListInterface $violations,
        ConstraintViolationInterface $violation
    ): void {
        $attributeData = [
            'code' => 'Foo_bar',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $ensureFranklinAttributeGroupExists->ensureExistence();

        $factory->createAttribute('pim_catalog_text')->willReturn($attribute);
        $updater->update($attribute, $attributeData)->shouldBeCalled();
        $validator->validate($attribute)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(1);
        $violations->get(0)->willReturn($violation);
        $violation->getMessage()->willReturn('validation message');

        $saver->save($attribute)->shouldNotBeCalled();

        $this
            ->shouldThrow(new \Exception('validation message'))
            ->during(
                'create',
                [
                    AttributeCode::fromLabel('Foo bar'),
                    new AttributeLabel('Foo bar'),
                    new AttributeType('pim_catalog_text')
                ]
            );
    }
}
