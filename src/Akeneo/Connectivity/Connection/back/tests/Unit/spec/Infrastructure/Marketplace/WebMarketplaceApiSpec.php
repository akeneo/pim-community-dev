<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceApiSpec extends ObjectBehavior
{
    public function let(
        Client $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        LoggerInterface $logger,
        FeatureFlag $fakeAppsFeatureFlag
    ): void {
        $this->beConstructedWith($client, $webMarketplaceAliases, $logger, $fakeAppsFeatureFlag);
        $this->setFixturePath(__DIR__ . '/fixtures/');
        $fakeAppsFeatureFlag->isEnabled()->willReturn(false);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebMarketplaceApi::class);
        $this->shouldImplement(WebMarketplaceApiInterface::class);
    }

    public function it_returns_extensions(
        Client $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        Response $response,
        StreamInterface $stream
    ): void {
        $expectedResponse = [
            'total' => 3,
            'limit' => 10,
            'offset' => 0,
            'items' => [
                [
                    'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'EIKONA Media GmbH',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'description_1',
                    'url' => 'url_1',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                ],
                [
                    'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                    'name' => 'Akeneo PIM Connector for Shopify',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                    'author' => 'StrikeTru',
                    'partner' => 'Akeneo Partner',
                    'description' => 'description_2',
                    'url' => 'url_2',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                ]
            ],
        ];

        $webMarketplaceAliases->getEdition()->willReturn('community-edition');
        $webMarketplaceAliases->getVersion()->willReturn('5.0');
        $stream->getContents()->willReturn(\json_encode($expectedResponse));
        $response->getBody()->willReturn($stream);
        $client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'extension_type' => 'connector',
                'edition' => 'community-edition',
                'version' => '5.0',
                'offset' => 0,
                'limit' => 10,
            ],
        ])->willReturn($response);

        $extensions = ($this->getExtensions())->getWrappedObject();

        Assert::assertEquals($expectedResponse, $extensions);
    }

    public function it_returns_true_when_a_code_challenge_is_valid(
        Client $client,
        Response $response,
        StreamInterface $stream
    ): void {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';

        $client->request('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
            'json' => [
                'code_identifier' => $codeIdentifier,
                'code_challenge' => $codeChallenge,
            ],
        ])->willReturn($response);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(\json_encode(['valid' => true]));

        $this->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge)->shouldReturn(true);
    }

    public function it_returns_false_when_a_code_challenge_is_invalid(
        Client $client,
        Response $response,
        StreamInterface $stream
    ): void {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';

        $client->request('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
            'json' => [
                'code_identifier' => $codeIdentifier,
                'code_challenge' => $codeChallenge,
            ],
        ])->willReturn($response);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(\json_encode(['valid' => false]));

        $this->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge)->shouldReturn(false);
    }

    public function it_returns_false_when_a_code_challenge_request_fails(
        Client $client,
        Response $response,
        StreamInterface $stream
    ): void {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';

        $client->request('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
            'json' => [
                'code_identifier' => $codeIdentifier,
                'code_challenge' => $codeChallenge,
            ],
        ])->willReturn($response);
        $response->getStatusCode()->willReturn(404);
        $response->getBody()->willReturn($stream);
        $stream->getContents()->willReturn(\json_encode(['error' => 'Not found.']));

        $this->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge)->shouldReturn(false);
    }

    public function it_returns_apps(
        Client $client,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        Response $response,
        StreamInterface $stream
    ): void {
        $expectedResponse = [
            'total' => 2,
            'limit' => 10,
            'offset' => 0,
            'items' => [
                [
                    'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'EIKONA Media GmbH',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'description_1',
                    'url' => 'url_1',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                ],
                [
                    'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                    'name' => 'Akeneo PIM Connector for Shopify',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                    'author' => 'StrikeTru',
                    'partner' => 'Akeneo Partner',
                    'description' => 'description_2',
                    'url' => 'url_2',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                ],
            ],
        ];

        $webMarketplaceAliases->getEdition()->willReturn('community-edition');
        $webMarketplaceAliases->getVersion()->willReturn('5.0');
        $stream->getContents()->willReturn(\json_encode($expectedResponse));
        $response->getBody()->willReturn($stream);
        $client->request('GET', '/api/1.0/extensions', [
            'query' => [
                'extension_type' => 'app',
                'edition' => 'community-edition',
                'version' => '5.0',
                'offset' => 0,
                'limit' => 10,
            ],
        ])->willReturn($response);

        $apps = ($this->getApps())->getWrappedObject();

        Assert::assertEquals($expectedResponse, $apps);
    }

    public function it_returns_fake_apps(
        FeatureFlag $fakeAppsFeatureFlag
    ): void {
        $fakeAppsFeatureFlag->isEnabled()->willReturn(true);

        $extensions = ($this->getApps())->getWrappedObject();

        Assert::assertEquals([
            'total' => 2,
            'offset' => 0,
            'limit' => 120,
            'items' => [
                [
                    'id' => '6ff52991-1144-45cf-933a-5c45ae58e71a',
                    'name' => 'Yell extension',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'Akeneo connectivity team',
                    'partner' => 'Akeneo',
                    'description' => 'Developed by the Akeneo team to demonstrate the different steps of an app activation. You can try it safely!',
                    'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => true,
                    'activate_url' => 'https://yell-extension-t2omu7tdaq-uc.a.run.app/activate',
                    'callback_url' => 'https://yell-extension-t2omu7tdaq-uc.a.run.app/oauth2',
                ],
                [
                    "id" => "b213fec1-02e6-4f88-9e2e-0ac86fa34d92",
                    "author" => "Akeneo",
                    "partner" => null,
                    "name" => "Akeneo Demo App in docker",
                    "activate_url" => "http://172.17.0.1:8090",
                    "callback_url" => "http://172.17.0.1:8090/callback",
                    "categories" => [
                        "Advertising"
                    ],
                    "logo" => "https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-ico-app-demoapp_0.jpeg?itok=U7JH_xFa",
                    "description" => "Apps are the best way to connect the third-party technology that you need to your Akeneo platform. The Akeneo Demo App will allow you to test out the connection experience. You can connect your PIM with the Demo App to see just how easy it is!",
                    "certified" => false,
                    "url" => "https://marketplace.akeneo.com/extension/akeneo-demo-app"
                ]
            ],
        ], $extensions);
    }
}
