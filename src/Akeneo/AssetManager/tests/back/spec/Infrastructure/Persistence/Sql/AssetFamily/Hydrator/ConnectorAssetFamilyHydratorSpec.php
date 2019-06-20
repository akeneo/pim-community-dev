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

use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorAssetFamilyHydrator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ConnectorAssetFamilyHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $connection
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAssetFamilyHydrator::class);
    }

    function it_hydrates_a_connector_asset_family() {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => 'test/image_1.jpg',
            'image_original_filename'     => 'image_1.jpg',
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
        ];

        $file = new FileInfo();
        $file->setKey('test/image_1.jpg');
        $file->setOriginalFilename('image_1.jpg');
        $image = Image::fromFileInfo($file);

        $expectedAssetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString('designer'),
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            $image
        );

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }

    function it_hydrates_an_asset_family_without_image() {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => null,
            'image_original_filename'     => null,
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ])
        ];

        $expectedAssetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString('designer'),
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            Image::createEmpty()
        );

        $this->hydrate($row)->shouldBeLike($expectedAssetFamily);
    }
}
