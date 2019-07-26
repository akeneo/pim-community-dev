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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\CreateAttribute;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
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
        SelectActiveLocaleCodesManagedByFranklinQueryInterface $activeLocaleCodesQuery,
        LoggerInterface $logger
    ): void {
        $activeLocaleCodesQuery->execute()->willReturn([new LocaleCode('en_US')]);

        $this->beConstructedWith(
            $factory,
            $updater,
            $saver,
            $validator,
            $ensureFranklinAttributeGroupExists,
            $activeLocaleCodesQuery,
            $logger
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

        $this->create(new Attribute(
            AttributeCode::fromLabel('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_text')
        ))->shouldReturn(null);
    }

    public function it_creates_multiple_attributes(
        $factory,
        $updater,
        $validator,
        $saver,
        $ensureFranklinAttributeGroupExists,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        ConstraintViolationListInterface $violations
    ): void {
        $attribute1Data = [
            'code' => 'Foo_bar',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];
        $attribute2Data = [
            'code' => 'Color',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Color'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $ensureFranklinAttributeGroupExists->ensureExistence()->shouldBeCalled();

        $factory->createAttribute('pim_catalog_text')->willReturn($attribute1, $attribute2);
        $updater->update($attribute1, $attribute1Data)->shouldBeCalled();
        $updater->update($attribute2, $attribute2Data)->shouldBeCalled();
        $validator->validate($attribute1)->willReturn($violations->getWrappedObject());
        $validator->validate($attribute2)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->saveAll([$attribute1, $attribute2])->shouldBeCalled();

        $attributes = [
            new Attribute(
                AttributeCode::fromLabel('Foo bar'),
                new AttributeLabel('Foo bar'),
                new AttributeType('pim_catalog_text')
            ),
            new Attribute(
                AttributeCode::fromLabel('Color'),
                new AttributeLabel('Color'),
                new AttributeType('pim_catalog_text')
            ),
        ];

        $this->bulkCreate($attributes)->shouldReturn($attributes);
    }

    public function it_creates_multiple_attributes_and_returns_only_the_attributes_successfully_created(
        $factory,
        $updater,
        $validator,
        $saver,
        $ensureFranklinAttributeGroupExists,
        AttributeInterface $pimAttribute1,
        AttributeInterface $pimAttribute2,
        ConstraintViolationListInterface $violations1,
        ConstraintViolationListInterface $violations2,
        ConstraintViolationInterface $violation
    ): void {
        $attribute1Data = [
            'code' => 'Foo_bar',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];
        $attribute2Data = [
            'code' => 'Color',
            'group' => FranklinAttributeGroup::CODE,
            'labels' => [
                'en_US' => 'Color'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $ensureFranklinAttributeGroupExists->ensureExistence()->shouldBeCalled();

        $factory->createAttribute('pim_catalog_text')->willReturn($pimAttribute1, $pimAttribute2);
        $updater->update($pimAttribute1, $attribute1Data)->shouldBeCalled();
        $updater->update($pimAttribute2, $attribute2Data)->shouldBeCalled();
        $validator->validate($pimAttribute1)->willReturn($violations1->getWrappedObject());
        $validator->validate($pimAttribute2)->willReturn($violations2->getWrappedObject());
        $violations1->count()->willReturn(1);
        $violations2->count()->willReturn(0);
        $violations1->get(0)->willReturn($violation);
        $violation->getMessage()->willReturn('Invalid attribute');

        $saver->saveAll([$pimAttribute2])->shouldBeCalled();

        $attribute1 = new Attribute(
            AttributeCode::fromLabel('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_text')
        );
        $attribute2 = new Attribute(
            AttributeCode::fromLabel('Color'),
            new AttributeLabel('Color'),
            new AttributeType('pim_catalog_text')
        );

        $this->bulkCreate([$attribute1, $attribute2])->shouldReturn([$attribute2]);
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

        $this->create(new Attribute(
            AttributeCode::fromLabel('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_number')
        ))->shouldReturn(null);
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
                    new Attribute(
                        AttributeCode::fromLabel('Foo bar'),
                        new AttributeLabel('Foo bar'),
                        new AttributeType('pim_catalog_text')
                    )
                ]
            );
    }
}
