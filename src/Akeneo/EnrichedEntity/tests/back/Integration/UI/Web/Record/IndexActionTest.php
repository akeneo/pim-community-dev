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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Record;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_LIST_ROUTE = 'akeneo_reference_entities_record_index_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
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
    public function it_returns_a_list_of_records()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_LIST_ROUTE,
            ['referenceEntityIdentifier' => 'designer']
        );

        $expectedContent = json_encode([
            'items' => [
                [
                    'identifier'                 => 'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
                    'reference_entity_identifier' => 'designer',
                    'code' => 'starck',
                    'labels'                     => [
                        'en_US' => 'Philippe Starck',
                    ],
                    'image' => null
                ],
                [
                    'identifier'                 => 'designer_coco_a1677570-a278-444b-ab46-baa1db199392',
                    'reference_entity_identifier' => 'designer',
                    'code' => 'coco',
                    'labels'                     => [
                        'en_US' => 'Coco',
                    ],
                    'image' => null
                ],
            ],
            'total' => 2,
        ]);
        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_OK, $expectedContent);
    }

    private function loadFixtures(): void
    {
        $findRecordItems = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_items_for_reference_entity');
        $findRecordItems->save(
            $this->createRecordItem(
                'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
                'designer',
                'starck',
                [ 'en_US' => 'Philippe Starck']
            )
        );
        $findRecordItems->save(
            $this->createRecordItem(
                'designer_coco_a1677570-a278-444b-ab46-baa1db199392',
                'designer',
                'coco',
                ['en_US' => 'Coco']
            )
        );

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }

    private function createRecordItem(
        string $recordIdentifier,
        string $referenceEntityIdentifier,
        string $code,
        array $labels
    ): RecordItem {
        $recordItem = new RecordItem();
        $recordItem->identifier = RecordIdentifier::fromString($recordIdentifier);
        $recordItem->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $recordItem->code = RecordCode::fromString($code);
        $recordItem->labels = LabelCollection::fromArray($labels);
        $recordItem->image = Image::createEmpty();

        return $recordItem;
    }
}
