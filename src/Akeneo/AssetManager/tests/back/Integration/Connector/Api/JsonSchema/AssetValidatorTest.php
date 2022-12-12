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

namespace Akeneo\AssetManager\Integration\Connector\Api\JsonSchema;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema\AssetValidator;
use Akeneo\AssetManager\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class AssetValidatorTest extends SqlIntegrationTestCase
{
    private AssetValidator $assetValidator;

    private AttributeRepositoryInterface $attributeRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private int $attributeOrder;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetValidator = $this->get('akeneo_assetmanager.infrastructure.connector.api.asset_validator');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeOrder = 2;

        $this->resetDB();
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->assetFamily('country')
            ->load();

        $this->fixturesLoader
            ->assetFamily('designer')
            ->load();

        $this->fixturesLoader
            ->assetFamily('brand')
            ->withAttributes([
                'long_description',     // text
                'main_image',           // image
                'main_material',        // option
                'materials',            // option collection
                'year',                 // number
                'website'               // media_link
            ])
            ->load();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_asset_structure_is_valid()
    {
        $asset = [
            'code' => 'kartell',
            'values' => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Kartell english label'
                    ]
                ],
                'long_description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'This famous Italian company has revolutionised plastic.',
                    ],
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                        'data'    => null,
                    ],
                ],
                'main_image' => [
                    [
                        'locale'  => null,
                        'channel' => 'mobile',
                        'data'    => 'images/kartell_small.jpg',
                    ],
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => 'images/kartell_large.jpg',
                    ],
                ],
                'main_material' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 'plastic',
                    ],
                ],
                'materials' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => [
                            'plastic',
                            'wool',
                            'wood',
                        ],
                    ],
                ],
                'year' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => '1949',
                    ],
                ],
                'website' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => 'id-screenshot-website650'
                    ]
                ]
            ],
        ];

        $errors = $this->assetValidator->validate(AssetFamilyIdentifier::fromString('brand'), $asset);

        $this->assertSame([], $errors);
    }

    /**
     * @test
     */
    public function it_returns_all_the_validation_errors_of_the_asset_values()
    {
        $asset = [
            'code' => 'kartell',
            'values' => [
                'label' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => 'Kartell english label'
                    ]
                ],
                'long_description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'ecommerce',
                        'data'    => 'This famous Italian company has revolutionised plastic.',
                    ],
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                    ],
                ],
                'country' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 22,
                    ],
                ],
                'designers' => [
                    [
                        'locale'  => null,
                        'channel' => 'ecommerce',
                        'data'    => 'starck',
                    ],
                ],
                'main_image' => [
                    [
                        'channel' => 'mobile',
                        'data'    => 'images/kartell_small.jpg',
                    ],
                ],
                'main_material' => [
                    [
                        'locale' => null,
                        'data'   => 'plastic',
                    ],
                ],
                'materials' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => null,
                        'data'    => [
                            'lighting',
                            'home_accessories',
                            null,
                        ],
                    ],
                ],
                'year' => [
                    [
                        'locale'  => null,
                        'channel' => null,
                        'data'    => 1949,
                    ],
                ],
                'website' => [
                    [
                        'locale' => null,
                        'channel' => null,
                        'data' => 250
                    ]
                ]
            ],
        ];

        $errors = $this->assetValidator->validate(AssetFamilyIdentifier::fromString('brand'), $asset);
        $errors = JsonSchemaErrorsFormatter::format($errors);

        $this->assertCount(28, $errors);
        $this->assertContains(
            [
                'property' => 'values.long_description[1]',
                'message'  => 'The required properties (data) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.main_material[0]',
                'message'  => 'The required properties (channel) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.main_image[0]',
                'message'  => 'The required properties (locale) are missing'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.materials[0].data[2]',
                'message'  => 'The data (null) must match the type: string'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.year[0]',
                'message'  => 'The properties must match schema: data'
            ],
            $errors
        );
        $this->assertContains(
            [
                'property' => 'values.website[0].data',
                'message'  => 'The data (integer) must match the type: string, null'
            ],
            $errors
        );
    }

    /**
     * @test
     */
    public function it_does_not_validate_values_if_the_main_structure_is_invalid()
    {
        $asset = [
            'values' => [
                'foo' => 'bar',
                'description' => [
                    [
                        'locale'  => 'en_US',
                        'channel' => 'mobile',
                    ],
                ],
            ],
        ];
        $errors = $this->assetValidator->validate(AssetFamilyIdentifier::fromString('brand'), $asset);
        $errors = JsonSchemaErrorsFormatter::format($errors);

        $this->assertCount(1, $errors);
        $this->assertContains(
            [
                'property' => '',
                'message'  => 'The required properties (code) are missing'
            ],
            $errors
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}
