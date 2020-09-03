<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GuzzleWebhookClient;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestSender;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClientSpec extends ObjectBehavior
{
    function let(
        RequestFactory $requestFactory,
        RequestSender $requestSender
    )
    {
        $this->beConstructedWith($requestFactory, $requestSender);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GuzzleWebhookClient::class);
    }

    public function it_builds_requests($requestFactory): void
    {
        $webhookRequests = $this->getWebhookRequests();

        $requestFactory->create(
            $webhookRequests[0]->webhook()->url(),
            json_encode($webhookRequests[0]->event()->normalize()),
            ['secret' => $webhookRequests[0]->webhook()->secret()]
        )->shouldBeCalled();

        $requestFactory->create(
            $webhookRequests[1]->webhook()->url(),
            json_encode($webhookRequests[1]->event()->normalize()),
            ['secret' => $webhookRequests[1]->webhook()->secret()]
        )->shouldBeCalled();

        $this->bulkSend($webhookRequests);
    }

    /**
     * @return WebhookRequest[]
     */
    private function getWebhookRequests()
    {
        $products = [
            'productA' => [
                'identifier' => 'productA',
                'family' => 'familyA',
                'parent' => null,
                'groups' => ['groupA'],
                'categories' => ['categoryA1'],
                'enabled' => true,
                'values' => [
                    'a_text' => [
                        ['locale' => null, 'scope' => null, 'data' => 'this is a text',],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [
                    'PACK' => ['groups' => [], 'products' => ['bar', 'baz'], 'product_models' => []],
                    'UPSELL' => ['groups' => ['groupA'], 'products' => [], 'product_models' => []],
                    'X_SELL' => ['groups' => ['groupB'], 'products' => ['bar'], 'product_models' => []],
                    'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                ],
                'quantified_associations' => [],
            ],
            'productB' => [
                'identifier' => 'productB',
                'family' => 'familyB',
                'parent' => null,
                'groups' => ['groupB'],
                'categories' => ['categoryB'],
                'enabled' => true,
                'values' => [
                    'a_text' => [
                        ['locale' => null, 'scope' => null, 'data' => 'this is a text',],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [
                    'PACK' => ['groups' => [], 'products' => ['bar', 'baz'], 'product_models' => []],
                    'UPSELL' => ['groups' => ['groupA'], 'products' => [], 'product_models' => []],
                    'X_SELL' => ['groups' => ['groupB'], 'products' => ['bar'], 'product_models' => []],
                    'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                ],
                'quantified_associations' => [],
            ],
        ];

        $webhookEvents = [
            'eventA' => new WebhookEvent(
                'product.updated',
                'eventA-id',
                '2020-09-01T12:27:42+00:00',
                [
                    'identifier' => '594877',
                    'family' => 'familyA',
                    'parent' => NULL,
                    $products['productA']
                ]
            ),
            'eventB' => new WebhookEvent(
                'product.updated',
                'eventB-id',
                '2020-09-02T16:28:46+00:00',
                [
                    'identifier' => '594878',
                    'family' => 'familyB',
                    'parent' => NULL,
                    $products['productB']
                ]
            )
        ];

        $connectionWebhooks = [
            'connectionA' => new ConnectionWebhook(
                'magento',
                7,
                'magentoSecret',
                'http://172.17.0.1:8000/magentoWebhook'
            ),
            'connectionB' => new ConnectionWebhook(
                'erp',
                7,
                'erpSecret',
                'http://172.17.0.1:8000/erpWebhook'
            )
        ];

        return [
            new WebhookRequest($connectionWebhooks['connectionA'], $webhookEvents['eventA']),
            new WebhookRequest($connectionWebhooks['connectionB'], $webhookEvents['eventB']),
        ];
    }
}
