<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Test\Integration\ControllerIntegrationTestCase;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetReferenceEntityAttributesControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const ROUTE = 'pimee_tailored_export_get_reference_entity_attributes_action';
    private WebClientHelper $webClientHelper;
    private string $referenceEntityLabelIdentifier = '';
    private string $referenceEntityImageIdentifier = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->createReferenceEntity('designer');
    }

    public function test_it_returns_reference_entity_attributes(): void
    {
        $response = $this->callGetReferenceEntityAttributesRoute(
            ['reference_entity_code' => 'designer'],
            ['types' => ['text', 'image']],
        );
        $responseContent = json_decode($response->getContent(), true);

        Assert::assertSame([
            [
                'identifier' => $this->referenceEntityLabelIdentifier,
                'code' => 'label',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => true,
                'type' => 'text',
            ],
            [
                'identifier' => $this->referenceEntityImageIdentifier,
                'code' => 'image',
                'labels' => [],
                'value_per_channel' => false,
                'value_per_locale' => false,
                'type' => 'image',
            ],
        ], $responseContent);
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_it_throws_if_reference_entity_code_is_missing(): void
    {
        $response = $this->callGetReferenceEntityAttributesRoute([
            'reference_entity_code' => 'designer',
        ]);

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function callGetReferenceEntityAttributesRoute(array $routeArguments, array $params = []): Response
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE, $routeArguments, 'POST', $params);

        return $this->client->getResponse();
    }

    private function createReferenceEntity(string $referenceEntityIdentifier): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $referenceEntityRepository->create(ReferenceEntity::create(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty(),
        ));
        /** @var ReferenceEntity $referenceEntity */
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $this->referenceEntityLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier()->normalize();
        $this->referenceEntityImageIdentifier = $referenceEntity->getAttributeAsImageReference()->getIdentifier()->normalize();
    }
}
