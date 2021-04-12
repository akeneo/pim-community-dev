<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Record;

use Akeneo\ReferenceEntity\Common\Fake\MassDeleteRecordsLauncherSpy;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class MassDeleteRecordsActionTest extends ControllerIntegrationTestCase
{
    private const MASS_DELETE_RECORDS_ROUTE = 'akeneo_reference_entities_record_mass_delete_rest';

    private WebClientHelper $webClientHelper;
    private MassDeleteRecordsLauncherSpy $massDeleteRecordsLauncherSpy;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoreference_entity.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
        $this->massDeleteRecordsLauncherSpy = $this->get('akeneo_referenceentity.job.mass_delete_launcher');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_deletes_all_records(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'brand',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            [
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "reference_entity",
                        "value" => "brand",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ],
        );

        $this->webClientHelper->assert202Accepted($this->client->getResponse());
        $this->massDeleteRecordsLauncherSpy->hasLaunchedMassDelete(
            'brand',
            RecordQuery::createFromNormalized([
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "reference_entity",
                        "value" => "brand",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ])
        );
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_delete_records()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_reference_entity_identifiers_are_not_synced()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ],
            [
                "page" => 0,
                "size" => 50,
                "locale" => "en_US",
                "channel" => "ecommerce",
                "filters" => [
                    [
                        "field" => "reference_entity",
                        "value" => "brand",
                        "context" => [],
                        "operator" => "="
                    ]
                ]
            ]
        );
        $this->webClientHelper->assert400BadRequest($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::MASS_DELETE_RECORDS_ROUTE,
            [
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    private function forbidsEdit(): void
    {
        $this->get('akeneo_referenceentity.application.reference_entity_permission.can_edit_reference_entity_query_handler')
            ->forbid();
    }
    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_record_delete', true);
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_record_delete', false);
    }
}
