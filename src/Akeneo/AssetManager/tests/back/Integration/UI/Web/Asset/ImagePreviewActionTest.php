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

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
final class ImagePreviewActionTest extends ControllerIntegrationTestCase
{
    private const URL_VALUE_PREVIEW_ROUTE = 'akeneo_asset_manager_image_preview';
    private const FILENAME = '2016/04/Fred-site-web.jpg';

    private WebClientHelper $webClientHelper;

    private FixturesLoader $fixturesLoader;

    private MediaLinkAttribute $attribute;

    private CacheManager $cacheManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
        $this->fixturesLoader = $this->get('akeneoasset_manager.tests.helper.fixtures_loader');

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
                'data' => base64_encode(self::FILENAME),
                'attributeIdentifier' => $this->attribute->getIdentifier(),
                'type' => PreviewGeneratorRegistry::THUMBNAIL_TYPE
            ]
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertResponse($response, 200, '');
    }

    /**
     * @test
     */
    public function it_fallbacks_to_the_default_image_if_the_attribute_is_not_found(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::URL_VALUE_PREVIEW_ROUTE,
            [
                'data' => base64_encode(self::FILENAME),
                'attributeIdentifier' => 'unknown_attribute',
                'type' => PreviewGeneratorRegistry::THUMBNAIL_TYPE
            ]
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertResponse($response, 200, '');
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                 'website'
             ])
            ->load();
        $this->attribute = $fixtures['attributes']['website'];
    }
}
