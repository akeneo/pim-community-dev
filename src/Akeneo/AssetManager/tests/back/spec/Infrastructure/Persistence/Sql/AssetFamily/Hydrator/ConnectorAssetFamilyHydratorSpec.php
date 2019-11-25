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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorAssetFamilyHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorProductLinkRulesHydrator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ConnectorAssetFamilyHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $connection,
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        TransformationCollectionFactory $transformationCollectionFactory
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection, $productLinkRulesHydrator, $transformationCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAssetFamilyHydrator::class);
    }

    function it_hydrates_a_connector_asset_family(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        TransformationCollectionFactory $transformationCollectionFactory
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

        $transformationCollection = TransformationCollection::noTransformation();
        $transformationCollectionFactory->fromNormalized([
            ['fake_transformation'],
        ])->willReturn($transformationCollection);

        $expectedAssetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString('designer'),
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
            $transformationCollection
        );

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }

    function it_hydrates_an_asset_family_without_image(
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        TransformationCollectionFactory $transformationCollectionFactory
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
        ];

        $transformationCollection = TransformationCollection::noTransformation();
        $transformationCollectionFactory->fromNormalized([
            ['fake_transformation'],
        ])->willReturn($transformationCollection);

        $expectedAssetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString('designer'),
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            Image::createEmpty(),
            [],
            $transformationCollection
        );

        $productLinkRulesHydrator->hydrate([])->willReturn([]);

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }
}
