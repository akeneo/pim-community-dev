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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\PropertyAccessibleAsset;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\PropertyAccessibleAsset\PropertyAccessibleAssetHydrator;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PropertyAccessibleAssetHydratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PropertyAccessibleAssetHydrator::class);
    }

    public function it_can_hydrate_the_accessible_asset(
        TextAttribute $descriptionAttribute,
        TextAttribute $emailAttribute,
        TextAttribute $nameAttribute,
        OptionAttribute $tagAttribute
    ) {
        $descriptionAttribute->getCode()->willReturn(AttributeCode::fromString('description'));
        $emailAttribute->getCode()->willReturn(AttributeCode::fromString('email'));
        $nameAttribute->getCode()->willReturn(AttributeCode::fromString('name'));
        $tagAttribute->getCode()->willReturn(AttributeCode::fromString('tag'));

        $valueCollection = [
            'designer_description_1298389389492_ecommerce_en_US' => [
                'attribute' => 'designer_description_1298389389492',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'The description'
            ],
            'designer_name_1298389389492_en_US' => [
                'attribute' => 'designer_name_1298389389492',
                'channel' => null,
                'locale' => 'en_US',
                'data' => 'The name'
            ],
            'designer_email_1298389389492_ecommerce' => [
                'attribute' => 'designer_email_1298389389492',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => 'The email'
            ],
            'designer_tag_1298389389492' => [
                'attribute' => 'designer_tag_1298389389492',
                'channel' => null,
                'locale' => null,
                'data' => ['paul', 'michel']
            ]
        ];
        $rawAsset = [
            'code'             => 'iphone',
            'value_collection' => json_encode($valueCollection)
        ];
        $indexedAttributes =  [
            'designer_description_1298389389492' => $descriptionAttribute,
            'designer_name_1298389389492' => $nameAttribute,
            'designer_email_1298389389492' => $emailAttribute,
            'designer_tag_1298389389492' => $tagAttribute,
        ];

        $propertyAccessibleAsset = $this->hydrate($rawAsset, $indexedAttributes);

        $propertyAccessibleAsset->shouldBeAnInstanceOf(PropertyAccessibleAsset::class);

        $propertyAccessibleAsset->getValue('description-ecommerce-en_US')->shouldReturn('The description');
        $propertyAccessibleAsset->getValue('name-en_US')->shouldReturn('The name');
        $propertyAccessibleAsset->getValue('email-ecommerce')->shouldReturn('The email');
        $propertyAccessibleAsset->getValue('tag')->shouldReturn(['paul', 'michel']);
    }

    public function it_skips_attribute_if_it_does_not_exist_anymore(TextAttribute $emailAttribute)
    {
        $emailAttribute->getCode()->willReturn(AttributeCode::fromString('email'));

        $valueCollection = [
            'designer_email_1298389389492_ecommerce' => [
                'attribute' => 'designer_email_1298389389492',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => 'The email'
            ],
            'designer_tag_1298389389492' => [
                'attribute' => 'designer_tag_1298389389492',
                'channel' => null,
                'locale' => null,
                'data' => ['paul', 'michel']
            ]
        ];
        $rawAsset = [
            'code'             => 'iphone',
            'value_collection' => json_encode($valueCollection)
        ];
        $indexedAttributes =  [
            'designer_email_1298389389492' => $emailAttribute,
        ];

        $propertyAccessibleAsset = $this->hydrate($rawAsset, $indexedAttributes);

        $propertyAccessibleAsset->shouldBeAnInstanceOf(PropertyAccessibleAsset::class);

        $propertyAccessibleAsset->getValue('email-ecommerce')->shouldReturn('The email');
        $propertyAccessibleAsset->hasValue('tag')->shouldReturn(false);
    }

    public function it_hydrates_asset_without_values()
    {
        $rawAsset = [
            'code'             => 'iphone',
            'value_collection' => json_encode([])
        ];
        $indexedAttributes =  [];

        $propertyAccessibleAsset = $this->hydrate($rawAsset, $indexedAttributes);

        $propertyAccessibleAsset->shouldBeAnInstanceOf(PropertyAccessibleAsset::class);
    }
}
