<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\AuthenticatedClientFactory;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const ENRICHED_ENTITIY_EDIT_ROUTE = 'akeneo_enriched_entities_enriched_entity_edit_rest';

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
        $this->webClientHelper = $this->get('akeneo_ee_integration_tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_edits_an_enriched_entity_details(): void
    {
        $postContent = [
            'identifier' => 'designer',
            'labels'     => [
                'en_US' => 'foo',
                'fr_FR' => 'bar',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_EDIT_ROUTE,
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
        $entityItem = $repository->getByIdentifier(EnrichedEntityIdentifier::fromString($postContent['identifier']));

        Assert::assertEquals(array_keys($postContent['labels']), $entityItem->getLabelCodes());
        Assert::assertEquals($postContent['labels']['en_US'], $entityItem->getLabel('en_US'));
        Assert::assertEquals($postContent['labels']['fr_FR'], $entityItem->getLabel('fr_FR'));
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITIY_EDIT_ROUTE,
            ['identifier' => 'any_id'],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function getEnrichEntityRepository(): EnrichedEntityRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
    }

    private function loadFixtures(): void
    {
        $enrichedEntityRepository = $this->getEnrichEntityRepository();

        $entityItem = EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $enrichedEntityRepository->create($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
