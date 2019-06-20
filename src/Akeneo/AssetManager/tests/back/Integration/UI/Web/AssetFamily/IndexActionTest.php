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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\ReferenceEntity;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityItem;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const REFERENCE_ENTITY_LIST_ROUTE = 'akeneo_reference_entities_reference_entity_index_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_reference_entities(): void
    {
        $this->webClientHelper->callRoute($this->client, self::REFERENCE_ENTITY_LIST_ROUTE);

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
            'akeneo_referenceentity.infrastructure.persistence.query.find_reference_entity_items'
        );

        $file = new FileInfo();
        $file->setKey('/path/image.jpg');
        $file->setOriginalFilename('image.jpg');

        $entityItem = new ReferenceEntityItem();
        $entityItem->identifier = (ReferenceEntityIdentifier::fromString('designer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Designer',
        ]);
        $entityItem->image = Image::createEmpty();
        $queryHandler->save($entityItem);

        $entityItem = new ReferenceEntityItem();
        $entityItem->identifier = (ReferenceEntityIdentifier::fromString('manufacturer'));
        $entityItem->labels = LabelCollection::fromArray([
            'en_US' => 'Manufacturer',
            'fr_FR' => 'Fabricant',
        ]);
        $entityItem->image = Image::fromFileInfo($file);
        $queryHandler->save($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
