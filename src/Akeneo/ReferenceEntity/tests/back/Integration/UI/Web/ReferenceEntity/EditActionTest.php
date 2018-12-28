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

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const REFERENCE_ENTITY_EDIT_ROUTE = 'akeneo_reference_entities_reference_entity_edit_rest';

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
    public function it_edits_a_reference_entity_details(): void
    {
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ],
            'image'      => [
                'filePath'         => '/path/image.jpg',
                'originalFilename' => 'image.jpg'
            ]
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getEnrichEntityRepository();
        $entityItem = $repository->getByIdentifier(ReferenceEntityIdentifier::fromString($postContent['identifier']));

        Assert::assertEquals(array_keys($postContent['labels']), $entityItem->getLabelCodes());
        Assert::assertEquals($postContent['labels']['en_US'], $entityItem->getLabel('en_US'));
        Assert::assertEquals($postContent['labels']['fr_FR'], $entityItem->getLabel('fr_FR'));
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_EDIT_ROUTE,
            ['identifier' => 'brand'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier' => 'wrong_identifier',
                'labels'     => [
                    'en_US' => 'foo',
                    'fr_FR' => 'bar',
                ],
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST, '"Reference entity identifier provided in the route and the one given in the body of your request are different"');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_EDIT_ROUTE,
            ['identifier' => 'any_id'],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_access_denied_if_the_user_does_not_have_permissions(): void
    {
        $this->client->followRedirects(false);
        $this->forbidsEdit();
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::REFERENCE_ENTITY_EDIT_ROUTE,
            ['identifier' => 'designer'],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    private function getEnrichEntityRepository(): ReferenceEntityRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }

    private function loadFixtures(): void
    {
        $referenceEntityRepository = $this->getEnrichEntityRepository();

        $entityItem = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($entityItem);

        $fr = new Locale();
        $fr->setId(1);
        $fr->setCode('fr_FR');
        $this->get('pim_catalog.repository.locale')->save($fr);

        $activatedLocales = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }
}
