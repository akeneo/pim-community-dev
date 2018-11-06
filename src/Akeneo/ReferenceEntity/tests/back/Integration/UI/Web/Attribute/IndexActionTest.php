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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private const RESPONSES_DIR = 'Attribute/ListDetails/';

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
    public function it_lists_all_attributes_for_a_reference_entity(): void
    {
        $inMemoryFindAttributesDetailsQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_details');
        $inMemoryFindAttributesDetailsQuery->save($this->createNameAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createEmailAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createPortraitAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createFavoriteColorAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createColorsAttribute());
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok/designer.json');
    }

    /**
     * @test
     */
    public function it_lists_another_set_of_attributes_for_a_reference_entity(): void
    {
        $inMemoryFindAttributesDetailsQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_details');
        $inMemoryFindAttributesDetailsQuery->save($this->createNameAttribute());
        $inMemoryFindAttributesDetailsQuery->save($this->createPortraitAttribute());
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'ok/name_portrait.json');
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list_if_the_reference_entity_does_not_have_any_attributes(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'empty_list.json');
    }

    /**
     * @test
     */
    public function it_returns_a_not_found_response_when_the_reference_entity_identifier_does_not_exists(): void
    {
        $this->webClientHelper->assertRequest($this->client, self::RESPONSES_DIR . 'not_found.json');
    }

    private function loadFixtures(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_referenceentity_attribute_create', true);

        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        ));
        $referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        ));
    }

    private function createNameAttribute(): AttributeDetails
    {
        $nameAttribute = new AttributeDetails();
        $nameAttribute->identifier = sprintf('name_designer_%s', md5('fingerprint'));
        $nameAttribute->referenceEntityIdentifier = 'designer';
        $nameAttribute->type = 'text';
        $nameAttribute->code = 'name';
        $nameAttribute->labels = ['en_US' => 'Name'];
        $nameAttribute->order = 0;
        $nameAttribute->isRequired = true;
        $nameAttribute->valuePerChannel = true;
        $nameAttribute->valuePerLocale = true;
        $nameAttribute->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => true,
            'is_rich_text_editor' => true,
            'validation_rule' => 'none',
            'regular_expression' => null,
        ];

        return $nameAttribute;
    }

    private function createEmailAttribute()
    {
        $emailAttribute = new AttributeDetails();
        $emailAttribute->identifier = sprintf('email_designer_%s', md5('fingerprint'));
        $emailAttribute->referenceEntityIdentifier = 'designer';
        $emailAttribute->type = 'text';
        $emailAttribute->code = 'email';
        $emailAttribute->labels = ['en_US' => 'Email'];
        $emailAttribute->order = 0;
        $emailAttribute->isRequired = true;
        $emailAttribute->valuePerChannel = true;
        $emailAttribute->valuePerLocale = true;
        $emailAttribute->additionalProperties = [
            'max_length' => 155,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => 'email',
            'regular_expression' => null,
        ];

        return $emailAttribute;
    }

    private function createPortraitAttribute(): AttributeDetails
    {
        $portraitAttribute = new AttributeDetails();
        $portraitAttribute->identifier = sprintf('portrait_designer_%s', md5('fingerprint'));
        $portraitAttribute->referenceEntityIdentifier = 'designer';
        $portraitAttribute->type = 'image';
        $portraitAttribute->code = 'portrait';
        $portraitAttribute->labels = ['en_US' => 'Portrait'];
        $portraitAttribute->order = 1;
        $portraitAttribute->isRequired = true;
        $portraitAttribute->valuePerChannel = true;
        $portraitAttribute->valuePerLocale = true;
        $portraitAttribute->additionalProperties = [
            'max_file_size' => '1000',
            'allowed_extensions' => ['pdf'],
        ];

        return $portraitAttribute;
    }

    private function createFavoriteColorAttribute(): AttributeDetails
    {
        $optionAttribute = new AttributeDetails();
        $optionAttribute->identifier = sprintf('favorite_color_designer_%s', md5('fingerprint'));
        $optionAttribute->referenceEntityIdentifier = 'designer';
        $optionAttribute->type = 'option';
        $optionAttribute->code = 'favorite_color';
        $optionAttribute->labels = ['en_US' => 'Favorite color'];
        $optionAttribute->order = 2;
        $optionAttribute->isRequired = true;
        $optionAttribute->valuePerChannel = true;
        $optionAttribute->valuePerLocale = true;
        $optionAttribute->additionalProperties = [
            'options' => [
                [
                    'code'   => 'red',
                    'labels' => [
                        'en_US' => 'Red',
                        'fr_FR' => 'Rouge',
                    ],
                ],
                [
                    'code'   => 'green',
                    'labels' => [
                        'en_US' => 'Green',
                        'fr_FR' => 'Vert',
                    ],
                ],
            ],
        ];

        return $optionAttribute;
    }

    private function createColorsAttribute(): AttributeDetails
    {
        $optionCollectionAttribute = new AttributeDetails();
        $optionCollectionAttribute->identifier = sprintf('colors_designer_%s', md5('fingerprint'));
        $optionCollectionAttribute->referenceEntityIdentifier = 'designer';
        $optionCollectionAttribute->type = 'option_collection';
        $optionCollectionAttribute->code = 'colors';
        $optionCollectionAttribute->labels = ['en_US' => 'Colors'];
        $optionCollectionAttribute->order = 3;
        $optionCollectionAttribute->isRequired = true;
        $optionCollectionAttribute->valuePerChannel = true;
        $optionCollectionAttribute->valuePerLocale = true;
        $optionCollectionAttribute->additionalProperties = [
            'options' => [
                [
                    'code'   => 'red',
                    'labels' => [
                        'en_US' => 'Red',
                        'fr_FR' => 'Rouge',
                    ],
                ],
                [
                    'code'   => 'blue',
                    'labels' => [
                        'en_US' => 'Blue',
                        'fr_FR' => 'Bleu',
                    ],
                ],
            ],
        ];

        return $optionCollectionAttribute;
    }
}
