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

namespace spec\Akeneo\AssetManager\Domain\Query\AssetFamily\Connector;

use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use PhpSpec\ObjectBehavior;

class ConnectorAssetFamilySpec extends ObjectBehavior
{
    function let()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('starck');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ]);

        $this->beConstructedWith(
            $assetFamilyIdentifier,
            $labelCollection,
            Image::createEmpty(),
            [
                [
                    'product_selections' => [
                        [
                            'field' => 'sku',
                            'operator' => 'EQUALS',
                            'value' => '{{product_ref}}',
                        ],
                    ],
                    'assign_assets_to' => [
                        [
                            'attribute' => 'user_instructions',
                            'locale' => '{{locale}}',
                            'mode' => 'replace',
                        ],
                    ]
                ]
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAssetFamily::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'starck',
            'labels'                   => [
                'en_US' => 'Stark',
                'fr_FR' => 'Stark',
            ],
            'image' => null,
            'product_link_rules' => [
                [
                    'product_selections' => [
                        [
                            'field' => 'sku',
                            'operator' => 'EQUALS',
                            'value' => '{{product_ref}}',
                        ],
                    ],
                    'assign_assets_to' => [
                        [
                            'attribute' => 'user_instructions',
                            'locale' => '{{locale}}',
                            'mode' => 'replace',
                        ],
                    ]
                ]
            ]
        ]);
    }
}
