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

use Akeneo\AssetManager\Common\Helper\AuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\MediaLinkImageGenerator;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
final class ImagePreviewActionTest extends ControllerIntegrationTestCase
{
    private const URL_VALUE_PREVIEW_ROUTE = 'akeneo_asset_manager_image_preview';
    private const FILENAME = 'Akeneo-DSC_2109-2.jpg';

    /* @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var MediaLinkAttribute */
    private $attribute;

    /** @var CacheManager */
    private $cacheManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
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
                'data'                => self::FILENAME,
                'attributeIdentifier' => $this->attribute->getIdentifier(),
                'type'                => MediaLinkImageGenerator::THUMBNAIL_TYPE
            ]
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertResponse($response, 301, '');
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
