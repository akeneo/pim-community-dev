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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity\Hydrator\ConnectorReferenceEntityHydrator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use PhpSpec\ObjectBehavior;

class ConnectorReferenceEntityHydratorSpec extends ObjectBehavior
{
    function let(
        Connection $connection
    ) {
        $connection->getDatabasePlatform()->willReturn(new MySqlPlatform());
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorReferenceEntityHydrator::class);
    }

    function it_hydrates_a_connector_reference_entity() {
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

        $expectedReferenceEntity = new ConnectorReferenceEntity(
            ReferenceEntityIdentifier::fromString('designer'),
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            $image
        );

        $this->hydrate($row)->shouldBeLike($expectedReferenceEntity);
    }

    function it_does_not_hydrate_unexpected_values() {
        $row = [
            'identifier'                  => 'designer',
            'image_file_key'              => null,
            'image_original_filename'     => null,
            'labels'                      => json_encode([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ])
        ];

        $expectedReferenceEntity = new ConnectorReferenceEntity(
            ReferenceEntityIdentifier::fromString('designer'),
            LabelCollection::fromArray([
                'en_US' => 'Designer',
                'fr_FR' => 'Designer',
            ]),
            Image::createEmpty()
        );

        $this->hydrate($row)->shouldBeLike($expectedReferenceEntity);
    }
}
