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

namespace Akeneo\ReferenceEntity\Integration\UI\Web\Attribute;

use Akeneo\ReferenceEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ATTRIBUTE_ROUTE = 'akeneo_reference_entities_attribute_create_rest';

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
    public function it_creates_a_text_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_text_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_image_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_image_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_a_record_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_record_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_a_record_collection_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_record_collection_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_option_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_option_ok.json');
    }

    /**
     * @test
     */
    public function it_creates_an_option_collection_attribute(): void
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/attribute_option_collection_ok.json');
    }


    /**
     * @test
     */
    public function it_returns_an_error_if_the_code_is_invalid()
    {
        $this->webClientHelper->assertRequest($this->client, 'Attribute/Create/invalid_code.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'referenceEntityIdentifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_create', true);

        $activatedLocales = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_activated_locales_by_identifiers');
        $activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brands'),
            [],
            Image::createEmpty()
        );
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create($referenceEntity);
    }
}
