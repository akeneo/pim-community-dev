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
use Akeneo\ReferenceEntity\Common\Helper\FixturesLoader;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Filesystem\PreviewGenerator\UrlImageGenerator;
use Akeneo\ReferenceEntity\Integration\ControllerIntegrationTestCase;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
final class ImagePreviewActionTest extends ControllerIntegrationTestCase
{
    private const URL_VALUE_PREVIEW_ROUTE = 'akeneo_reference_entities_image_preview';
    private const FILENAME = 'Akeneo-DSC_2109-2.jpg';

    /* @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var UrlAttribute */
    private $attribute;

    /** @var CacheManager */
    private $cacheManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
        $this->fixturesLoader = $this->get('akeneoreference_entity.tests.helper.fixtures_loader');

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
                'attributeIdentifier' => $this->attribute->getIdentifier(),
                'type'                => UrlImageGenerator::THUMBNAIL_TYPE
            ]
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertResponse($response, 301, '');
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes([
                 'website'
             ])
            ->load();
        $this->attribute = $fixtures['attributes']['website'];
    }
}
