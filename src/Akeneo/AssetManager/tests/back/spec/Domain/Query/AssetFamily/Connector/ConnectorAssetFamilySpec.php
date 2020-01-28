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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use PhpSpec\ObjectBehavior;

class ConnectorAssetFamilySpec extends ObjectBehavior
{
    function let()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('starck');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark',
        ]);

        $transformation = new ConnectorTransformation(
            TransformationLabel::fromString('label'),
            Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => null]),
            OperationCollection::create([
                ThumbnailOperation::create(['width' => 100, 'height' => 80]),
            ]),
            '',
            '_2'
        );

        $namingConvention = NamingConvention::createFromNormalized([
            'source' => [
                'property' => 'code',
                'locale' => null,
                'channel' => null,
            ],
            'pattern' => '/^(<?product_ref>\w+)-(<?attribute>\w+).png$/',
            'abort_asset_creation_on_error' => false,
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
            ],
            new ConnectorTransformationCollection([$transformation]),
            $namingConvention,
            AttributeCode::fromString('media')
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
            'attribute_as_main_media' => 'media',
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
            ],
            'transformations' => [
                [
                    'label' => 'label',
                    'source' => [
                        'attribute' => 'main',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'target' => [
                        'attribute' => 'target',
                        'channel' => null,
                        'locale' => null,
                    ],
                    'operations' => [
                        [
                            'type' => 'thumbnail',
                            'parameters' => ['width' => 100, 'height' => 80],
                        ],
                    ],
                    'filename_prefix' => '',
                    'filename_suffix' => '_2',
                ],
            ],
            'naming_convention' => [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null,
                ],
                'pattern' => '/^(<?product_ref>\w+)-(<?attribute>\w+).png$/',
                'abort_asset_creation_on_error' => false,
            ],
        ]);
    }
}
