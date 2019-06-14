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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Prefix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\Suffix;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
final class ImagePreviewActionTest extends ControllerIntegrationTestCase
{
    private const URL_VALUE_PREVIEW_ROUTE = 'akeneo_reference_entities_image_preview';
    private const DAM_URL = 'https://akeneodemo.getbynder.com/m/1e567bef001b08fa/';
    private const FILENAME = 'Akeneo-DSC_2109-2.jpg';

    /* @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var CacheManager */
    private $cacheManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $this->loadFixtures();
    }

    public function tearDown(): void
    {
        $this->cacheManager = $this->get('liip_imagine.cache.manager');
        $this->cacheManager->remove();
    }

    /**
     * @test
     */
    public function it_fetches_the_binary_preview_for_a_value_an_attribute_and_type(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::URL_VALUE_PREVIEW_ROUTE,
            [
                'data'                => self::FILENAME,
                'attributeIdentifier' => 'dam_image_designer_fingerprint',
                'type'                => 'dam_thumbnail_small'
            ]
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertResponse($response, 301, '');
    }

    private function loadFixtures(): void
    {
        $attributeIdentifier = AttributeIdentifier::fromString('dam_image_designer_fingerprint');
        $attribute = UrlAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('dam_image'),
            LabelCollection::fromArray(['fr_FR' => 'DAM Image']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString(self::DAM_URL),
            Suffix::empty(),
            MediaType::fromString(MediaType::IMAGE)
        );
        $this->attributeRepository->create($attribute);
    }
}
