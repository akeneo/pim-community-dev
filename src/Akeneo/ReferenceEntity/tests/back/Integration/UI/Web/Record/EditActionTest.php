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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_EDIT_ROUTE = 'akeneo_reference_entities_record_edit_rest';
    private const RESPONSES_DIR = 'Record/Edit/';

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
    public function it_edits_a_record_details(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'details_ok.json');
    }

    /**
     * @test
     */
    public function it_edits_a_record_details_by_removing_the_default_image(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'remove_image_ok.json');
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'celine_dion',
                'referenceEntityIdentifier' => 'singer',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_errors_if_we_send_a_bad_request()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'invalid_image.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'unsynchronised_record_identifier.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_reference_entity_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'unsynchronised_reference_entity_identifier.json');
    }

    /**
     * @test
     */
    public function it_edits_a_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'text_value_ok.json');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_we_send_an_invalid_text_value()
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'invalid_text_value.json');
    }

//    /**
//     * @test
//     */
//    public function it_edits_a_file_value()
//    {
//        // TODO
//    }
//
//    /**
//     * @test
//     */
//    public function it_returns_an_error_if_we_send_an_invalid_file_value()
//    {
//        // TODO
//    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
    }

    private function loadFixtures(): void
    {
        $repository = $this->getRecordRepository();

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('singer');
        $recordCode = RecordCode::fromString('celine_dion');

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg');

        $entityItem = Record::create(
            RecordIdentifier::fromString('singer_celine_dion_a1677570-a278-444b-ab46-baa1db199392'),
            $referenceEntityIdentifier,
            $recordCode,
            [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Celine Dion',
            ],
            Image::fromFileInfo($imageInfo),
            ValueCollection::fromValues([])
        );
        $repository->create($entityItem);

        // text attribute
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('singer', 'website', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('singer'),
            AttributeCode::fromString('website'),
            LabelCollection::fromArray(['en_US' => 'Website']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::fromString(AttributeValidationRule::URL),
            AttributeRegularExpression::createEmpty()
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($textAttribute);

        // file attribute
        $fileAttribute = ImageAttribute::create(
            AttributeIdentifier::create('singer', 'live_picture', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('singer'),
            AttributeCode::fromString('live_picture'),
            LabelCollection::fromArray(['en_US' => 'Live picture']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['png'])
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->create($fileAttribute);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
