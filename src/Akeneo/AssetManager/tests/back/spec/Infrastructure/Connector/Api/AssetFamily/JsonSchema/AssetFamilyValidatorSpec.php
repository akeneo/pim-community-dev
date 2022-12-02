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

namespace spec\Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema;

use Akeneo\AssetManager\Infrastructure\Connector\Api\AssetFamily\JsonSchema\AssetFamilyValidator;
use PhpSpec\ObjectBehavior;

class AssetFamilyValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetFamilyValidator::class);
    }

    function it_does_not_return_any_error_when_the_asset_family_is_valid()
    {
        $assetFamily = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            /** /!\ /!\ /!\ /!\
             * Crappy fix to remove the possibility of updating the image of the asset family on the API side.
             * @todo : To remove if the functional decide to not have an image on the asset family
             * @todo : Check the PR https://github.com/akeneo/pim-enterprise-dev/pull/6651 for real fix
             */
//            'image' => 'images/starck.png',
            'product_link_rules' => [
                [
                    'product_selections' => [
                        [
                            'field'    => 'sku',
                            'operator' => '=',
                            'value'    => '{{product_sku}}'
                        ]
                    ],
                    'assign_assets_to' => [
                        [
                            'mode'      => 'add',
                            'attribute' => '{{attribute}}'
                        ]
                    ]
                ]
            ],
            'transformations' => [
                [
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'thumbnail', 'channel' => null, 'locale' => null],
                    'operations' => [
                        ['type' => 'colorspace'],
                    ],
                    'filename_suffix' => '_2',
                ],
            ],
            'naming_convention' => [
                'source' => ['property' => 'title', 'locale' => 'en_US', 'channel' => null],
                'pattern' => '/the_pattern/',
                'abort_asset_creation_on_error' => true,
            ],
            '_links'  => [
                'image_download' => [
                    'href' => 'http://localhost/api/rest/v1/asset-media-files/images/starck.png'
                ]
            ]
        ];

        $this->validate($assetFamily)->shouldReturn([]);
    }

    function it_does_not_return_any_error_when_no_labels()
    {
        $assetFamily = [
            'code' => 'starck',
            'labels' => (object) []
        ];

        $this->validate($assetFamily)->shouldReturn([]);
    }

    function it_is_only_mandatory_to_provide_the_code_of_the_asset_family()
    {
        $assetFamily = [
            'code' => 'starck'
        ];

        $this->validate($assetFamily)->shouldReturn([]);
    }

    function it_returns_errors_when_code_is_not_a_string()
    {
        $assetFamily = [
            'code' => []
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_errors_when_labels_has_a_wrong_format()
    {
        $assetFamily = [
            'code' => 'starck',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    function it_returns_an_error_when_code_is_not_provided()
    {
        $errors = $this->validate([]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_an_additional_property_is_filled()
    {
        $assetFamily = [
            'code' => 'starck',
            'unknown_property' => 'michel'
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_image_is_not_a_string_or_null()
    {
        $assetFamily = [
            'code' => 'starck',
            'image' => 42
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    public function it_returns_an_error_when_rule_templates_is_not_an_array()
    {
        $assetFamily = [
            'code' => 'starck',
            'rule_templates' => 'wrong_rule'
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    public function it_returns_an_error_when_rule_templates_does_not_have_conditions()
    {
        $assetFamily = [
            'code' => 'starck',
            'rule_templates' => [
                [
                    'actions' => [
                        [
                            'type' => 'add',
                            'field' => '{{attribute}}',
                            'value' => '{{code}}'
                        ]
                    ]
                ]
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    public function it_returns_an_error_when_rule_templates_does_not_have_actions()
    {
        $assetFamily = [
            'code' => 'starck',
            'rule_templates' => [
                [
                    'conditions' => [
                        [
                            'field' => 'sku',
                            'operator' => '=',
                            'value' => '{{product_sku}}'
                        ]
                    ]
                ]
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    public function it_returns_errors_when_rule_templates_has_null_values_on_a_property()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [
                [
                    'product_selections' => [
                        [
                            'field'    => 'sku',
                            'operator' => '=',
                            'value'    => null
                        ]
                    ],
                    'assign_assets_to' => [
                        [
                            'mode'      => 'add',
                            'attribute' => '{{attribute}}'
                        ]
                    ]
                ]
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(6);
    }

    public function it_returns_errors_when_rule_templates_has_additional_properties()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [
                [
                    'product_selections' => [
                        [
                            'field'    => 'sku',
                            'operator' => '=',
                            'value'    => '{{product_code}}',
                            'unknown_property' => 'michel'
                        ]
                    ],
                    'assign_assets_to' => [
                        [
                            'mode'      => 'add',
                            'attribute' => '{{attribute}}',
                            'unknown_property' => 'michel'
                        ]
                    ],
                    'unknown_property' => 'michel'
                ]
            ]
        ];

        $errors = $this->validate($assetFamily);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(7);
    }

    public function it_does_not_return_any_error_when_transformations_is_empty()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'transformations' => [],
        ];
        $this->validate($assetFamily)->shouldReturn([]);
    }

    public function it_returns_errors_when_a_transformation_is_invalid()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'transformations' => [
                [
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => ['invalid'], 'channel' => null, 'locale' => null],
                    'operations' => [
                        ['type' => 'colorspace'],
                    ],
                    'filename_suffix' => '_2',
                ],
            ],
        ];

        $errors = $this->validate($assetFamily);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(5);
    }

    public function it_returns_errors_when_a_transformation_has_additional_properties()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'transformations' => [
                [
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                    'operations' => [
                        ['type' => 'colorspace'],
                    ],
                    'filename_suffix' => '_2',
                    'foo' => 'bar',
                ],
            ],
        ];

        $errors = $this->validate($assetFamily);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    public function it_does_not_return_any_error_when_naming_convention_is_empty()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'naming_convention' => [],
        ];
        $this->validate($assetFamily)->shouldReturn([]);
    }

    public function it_returns_errors_when_naming_convention_is_invalid()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'naming_convention' => [
                'source' => [],
                'pattern' => '/the_pattern/',
                'abort_asset_creation_on_error' => true,
            ],
        ];

        $errors = $this->validate($assetFamily);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(3);
    }

    public function it_returns_errors_when_naming_convention_has_additional_properties()
    {
        $assetFamily = [
            'code' => 'starck',
            'product_link_rules' => [],
            'naming_convention' => [
                'source' => ['property' => 'title', 'locale' => 'en_US', 'channel' => null],
                'pattern' => '/the_pattern/',
                'abort_asset_creation_on_error' => true,
                'foo' => 'bar',
            ],
        ];

        $errors = $this->validate($assetFamily);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }
}
