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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkCreateAttributesInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkCreateAttributesInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject\AttributesToCreate;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkCreateAttributesInFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        CreateAttributeInterface $createAttribute,
        AddAttributeToFamilyInterface $addAttributeToFamily,
        FranklinAttributeCreatedRepositoryInterface $attributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $attributeAddedToFamilyRepository
    ) {
        $this->beConstructedWith(
            $createAttribute,
            $addAttributeToFamily,
            $attributeCreatedRepository,
            $attributeAddedToFamilyRepository
        );
    }

    public function it_is_a_create_attribute_in_family_handler()
    {
        $this->shouldBeAnInstanceOf(BulkCreateAttributesInFamilyHandler::class);
    }

    public function it_creates_multiple_attributes_and_adds_them_to_the_family(
        $createAttribute,
        $addAttributeToFamily,
        $attributeCreatedRepository,
        $attributeAddedToFamilyRepository
    ) {
        $pimFamilyCode = new FamilyCode('my_family_code');

        $attributes = new AttributesToCreate([
            [
                'franklinAttributeLabel' => 'color',
                'franklinAttributeType' => 'text',
            ],
            [
                'franklinAttributeLabel' => 'height',
                'franklinAttributeType' => 'number',
            ],
            [
                'franklinAttributeLabel' => 'frequency',
                'franklinAttributeType' => 'metric',
            ],
        ]);

        $createAttribute->bulkCreate(
            [
                [
                    'attributeCode' => AttributeCode::fromLabel('color'),
                    'attributeLabel' => new AttributeLabel('color'),
                    'attributeType' => new AttributeType(AttributeTypes::TEXT),
                ],
                [
                    'attributeCode' => AttributeCode::fromLabel('height'),
                    'attributeLabel' => new AttributeLabel('height'),
                    'attributeType' => new AttributeType(AttributeTypes::NUMBER),
                ],
                [
                    'attributeCode' => AttributeCode::fromLabel('frequency'),
                    'attributeLabel' => new AttributeLabel('frequency'),
                    'attributeType' => new AttributeType(AttributeTypes::TEXT),
                ]
            ]
        )->shouldBeCalled();

        $attributeCreatedRepository
            ->saveAll(Argument::that(function($events) {
                return empty(array_filter($events, function ($event) {
                    return ! $event instanceof FranklinAttributeCreated;
                }));
            }))
            ->shouldBeCalled();

        $addAttributeToFamily
            ->bulkAddAttributesToFamily($pimFamilyCode, ['color', 'height', 'frequency'])
            ->shouldBeCalled();

        $attributeAddedToFamilyRepository
            ->saveAll(Argument::that(function($events) {
                return empty(array_filter($events, function ($event) {
                    return ! $event instanceof FranklinAttributeAddedToFamily;
                }));
            }))
            ->shouldBeCalled();

        $command = new BulkCreateAttributesInFamilyCommand(
            $pimFamilyCode,
            $attributes
        );
        $this->handle($command);
    }
}
