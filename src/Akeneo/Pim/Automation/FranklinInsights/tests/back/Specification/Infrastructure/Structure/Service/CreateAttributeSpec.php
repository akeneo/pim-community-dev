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

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\FindOrCreateFranklinAttributeGroupInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroupCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\ProposalUpsert;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Structure\Service\CreateAttribute;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
        FindOrCreateFranklinAttributeGroupInterface $findOrCreateFranklinAttributeGroup
    ): void {
        $this->beConstructedWith($factory, $updater, $saver, $validator, $findOrCreateFranklinAttributeGroup);
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
        $findOrCreateFranklinAttributeGroup,
        AttributeInterface $attribute,
        ConstraintViolationListInterface $violations
    ): void {
        $attributeGroupCode = new FranklinAttributeGroupCode();
        $attributeData = [
            'code' => 'Foo_bar',
            'group' => (string) $attributeGroupCode,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $findOrCreateFranklinAttributeGroup->findOrCreate()->willReturn($attributeGroupCode);

        $factory->createAttribute('pim_catalog_text')->willReturn($attribute);
        $updater->update($attribute, $attributeData)->shouldBeCalled();
        $validator->validate($attribute)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(0);
        $saver->save($attribute)->shouldBeCalled();

        $this->create(
            AttributeCode::fromString('Foo bar'),
            new AttributeLabel('Foo bar'),
            new AttributeType('pim_catalog_text')
        )->shouldReturn(null);
    }

    public function it_throws_an_exception_when_there_are_some_violations(
        $factory,
        $updater,
        $validator,
        $saver,
        $findOrCreateFranklinAttributeGroup,
        AttributeInterface $attribute,
        ConstraintViolationListInterface $violations
    ): void {
        $attributeGroupCode = new FranklinAttributeGroupCode();

        $attributeData = [
            'code' => 'Foo_bar',
            'group' => (string) $attributeGroupCode,
            'labels' => [
                'en_US' => 'Foo bar'
            ],
            'localizable' => false,
            'scopable' => false
        ];

        $findOrCreateFranklinAttributeGroup->findOrCreate()->willReturn($attributeGroupCode);

        $factory->createAttribute('pim_catalog_text')->willReturn($attribute);
        $updater->update($attribute, $attributeData)->shouldBeCalled();
        $validator->validate($attribute)->willReturn($violations->getWrappedObject());
        $violations->count()->willReturn(1);
        $saver->save($attribute)->shouldNotBeCalled();

        $this
            ->shouldThrow(new ViolationHttpException($violations->getWrappedObject()))
            ->during(
                'create',
                [
                    AttributeCode::fromString('Foo bar'),
                    new AttributeLabel('Foo bar'),
                    new AttributeType('pim_catalog_text')
                ]
            );
    }
}
