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
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const DELETE_ATTRIBUTE_ROUTE = 'akeneo_reference_entities_attribute_delete_rest';

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
    public function it_deletes_an_attribute_and_its_records_values(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeDeletionRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /** @test */
    public function it_returns_an_error_when_the_attribute_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => 'unknown',
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => 'name',
                'referenceEntityIdentifier' => 'celine_dion',
            ],
            'DELETE'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_user_does_not_have_the_permissions_to_edit_the_reference_entity()
    {
        $this->forbidsEdit();
        $this->webClientHelper->callRoute(
            $this->client,
            self::DELETE_ATTRIBUTE_ROUTE,
            [
                'attributeIdentifier' => sprintf('%s_%s_%s', 'name', 'designer', md5('fingerprint')),
                'referenceEntityIdentifier' => 'designer',
            ],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
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
        $attributeRepository = $this->getAttributeRepository();
        $referenceEntityRepository = $this->getReferenceEntityRepository();

        $referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString('designer'),
                [],
                Image::createEmpty()
            )
        );

        $attributeItem = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', md5('fingerprint')),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($attributeItem);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_delete', true);
    }

    private function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
    }

    private function getReferenceEntityRepository(): ReferenceEntityRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_delete', false);
    }
}
