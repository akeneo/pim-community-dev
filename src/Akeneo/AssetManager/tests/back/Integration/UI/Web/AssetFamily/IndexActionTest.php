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

namespace Akeneo\AssetManager\Integration\UI\Web\AssetFamily;

use Akeneo\AssetManager\Common\Helper\AuthenticatedClient;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyItem;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const ASSET_FAMILY_LIST_ROUTE = 'akeneo_asset_manager_asset_family_index_rest';

    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_asset_families(): void
    {
        $this->webClientHelper->callRoute($this->client, self::ASSET_FAMILY_LIST_ROUTE);

        $expectedContent = json_encode([
            'items' => [
                [
                    'identifier' => 'designer',
                    'labels'     => [
                        'en_US' => 'Designer',
                    ],
                    'image' => null
                ],
                [
                    'identifier' => 'manufacturer',
                    'labels'     => [
                        'en_US' => 'Manufacturer',
                        'fr_FR' => 'Fabricant',
                    ],
                    'image'      => [
                        'filePath'         => '/path/image.jpg',
                        'originalFilename' => 'image.jpg'
                    ]
                ],
            ],
            'total' => 2,
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), 200, $expectedContent);
    }

    private function loadFixtures(): void
    {
        $queryHandler = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_items'
        );

        $file = new FileInfo();
        $file->setKey('/path/image.jpg');
        $file->setOriginalFilename('image.jpg');

        $entityItem = new AssetFamilyItem();
        $entityItem->identifier = (AssetFamilyIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
        ]);
        $entityItem->image = Image::createEmpty();
        $queryHandler->save($entityItem);

        $entityItem = new AssetFamilyItem();
        $entityItem->identifier = (AssetFamilyIdentifier::fromString('manufacturer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Manufacturer',
            'fr_FR' => 'Fabricant',
        ]);
        $entityItem->image = Image::fromFileInfo($file);
        $queryHandler->save($entityItem);
    }
}
