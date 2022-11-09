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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorAssetFamilyHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorNamingConventionHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorProductLinkRulesHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorTransformationCollectionHydrator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ConnectorAssetFamilyHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $connection,
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator,
        FindActivatedLocalesInterface $findActivatedLocales
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $findActivatedLocales->findAll()->willReturn(['en_US', 'fr_FR']);
        $this->beConstructedWith(
            $connection,
            $productLinkRulesHydrator,
            $transformationCollectionHydrator,
            $namingConventionHydrator,
            $findActivatedLocales
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAssetFamilyHydrator::class);
    }

    function it_hydrates_a_connector_asset_family(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator
    ) {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => 'test/image_1.jpg',
            'image_original_filename'     => 'image_1.jpg',
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            'rule_templates' => json_encode([
                [
                    'conditions' => 'FAKE',
                    'actions' => 'FAKE',
                ]
            ]),
            'transformations' => json_encode([['fake_transformation']]),
            'naming_convention' => '{}',
            'attribute_as_main_media' => null,
        ];

        $file = new FileInfo();
        $file->setKey('test/image_1.jpg');
        $file->setOriginalFilename('image_1.jpg');
        $image = Image::fromFileInfo($file);

        $productLinkRulesHydrator->hydrate([
            [
                'conditions' => 'FAKE',
                'actions' => 'FAKE',
            ]
        ])->willReturn([
            [
                'product_selections' => 'FAKE',
                'assign_assets_to' => 'FAKE',
            ]
        ]);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $transformationCollectionHydrator->hydrate([['fake_transformation']], $assetFamilyIdentifier)
            ->willReturn(new ConnectorTransformationCollection([]));
        $namingConventionHydrator->hydrate([], $assetFamilyIdentifier)->willReturn(new NullNamingConvention());

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            $image,
            [
                [
                    'product_selections' => 'FAKE',
                    'assign_assets_to' => 'FAKE',
                ]
            ],
            new ConnectorTransformationCollection([]),
            new NullNamingConvention(),
            null
        );

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }

    function it_hydrates_an_asset_family_without_image(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator
    ) {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => null,
            'image_original_filename'     => null,
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            'rule_templates' => json_encode([]),
            'transformations' => json_encode([['fake_transformation']]),
            'naming_convention' => '{}',
            'attribute_as_main_media' => 'media',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $transformationCollectionHydrator->hydrate([['fake_transformation']], $assetFamilyIdentifier)
            ->willReturn(new ConnectorTransformationCollection([]));
        $namingConventionHydrator->hydrate([], $assetFamilyIdentifier)->willReturn(new NullNamingConvention());

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            Image::createEmpty(),
            [],
            new ConnectorTransformationCollection([]),
            new NullNamingConvention(),
            AttributeCode::fromString('media')
        );

        $productLinkRulesHydrator->hydrate([])->willReturn([]);

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }

    function it_hydrates_a_connector_asset_family_with_a_naming_convention(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator
    ) {
        $normalizedNamingConvention = [
            'source' => [
                'property' => 'code',
                'channel' => null,
                'locale' => null,
            ],
            'pattern' => '/(pattern).jpg/',
            'abort_asset_creation_on_error' => false,
        ];

        $row = [
            'identifier' => 'designer',
            'image_file_key' => null,
            'image_original_filename' => null,
            'labels' => json_encode(
                [
                    'en_US' => 'Designer',
                    'fr_FR' => 'Designer',
                ]
            ),
            'rule_templates' => json_encode([]),
            'transformations' => json_encode([['fake_transformation']]),
            'naming_convention' => json_encode($normalizedNamingConvention),
            'attribute_as_main_media' => 'instructions',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $transformationCollectionHydrator->hydrate([['fake_transformation']], $assetFamilyIdentifier)
                                         ->willReturn(new ConnectorTransformationCollection([]));
        $namingConvention = NamingConvention::createFromNormalized($normalizedNamingConvention);
        $namingConventionHydrator->hydrate($normalizedNamingConvention, $assetFamilyIdentifier)->willReturn($namingConvention);

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray(
                [
                    'en_US' => 'Designer',
                    'fr_FR' => 'Designer',
                ]
            ),
            Image::createEmpty(),
            [],
            new ConnectorTransformationCollection([]),
            $namingConvention,
            AttributeCode::fromString('instructions')
        );

        $productLinkRulesHydrator->hydrate([])->willReturn([]);

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }

    function it_hydrates_an_asset_family_with_only_labels_from_activated_locales(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator
    ) {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => null,
            'image_original_filename'     => null,
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
                'de_DE' => 'Ich bin ein designer',
                'it_IT' => 'Sono un designer'
            ]),
            'rule_templates' => json_encode([]),
            'transformations' => json_encode([['fake_transformation']]),
            'naming_convention' => '{}',
            'attribute_as_main_media' => 'media',
        ];

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $transformationCollectionHydrator->hydrate([['fake_transformation']], $assetFamilyIdentifier)
            ->willReturn(new ConnectorTransformationCollection([]));
        $namingConventionHydrator->hydrate([], $assetFamilyIdentifier)->willReturn(new NullNamingConvention());

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            Image::createEmpty(),
            [],
            new ConnectorTransformationCollection([]),
            new NullNamingConvention(),
            AttributeCode::fromString('media')
        );

        $productLinkRulesHydrator->hydrate([])->willReturn([]);

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }
}
