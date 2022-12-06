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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
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
    private ?AttributeIdentifier $descriptionIdentifier = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('julia', $this->client);
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->createReferenceEntity();
    }

    public function test_it_returns_supported_reference_entity_attributes(): void
    {
        $response = $this->callGetReferenceEntityAttributesRoute(['reference_entity_code' => 'designer']);
        $responseContent = json_decode($response->getContent(), true);

        Assert::assertSame([
            [
                'identifier' => (string) $this->descriptionIdentifier,
                'code' => 'description',
                'labels' => [
                    'en_US' => 'Description',
                ],
                'value_per_channel' => false,
                'value_per_locale' => true,
                'type' => 'text',
            ],
        ], $responseContent);
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function callGetReferenceEntityAttributesRoute(array $routeArguments): Response
    {
        $this->webClientHelper->callApiRoute($this->client, self::ROUTE, $routeArguments, 'GET');

        return $this->client->getResponse();
    }

    private function createReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntityRepository->create(ReferenceEntity::create($referenceEntityIdentifier, [], Image::createEmpty()));

        $this->descriptionIdentifier = AttributeIdentifier::create('designer', 'description', 'fingerprint');
        $description = TextAttribute::createText(
            $this->descriptionIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty(),
        );
        $attributesRepository->create($description);
    }
}
