<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\WebhookBundle\Client;

use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestSender;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Assert;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestSenderSpec extends ObjectBehavior
{
    function let(
        LoggerInterface $logger
    ) {
        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create();
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->beConstructedWith($client, $logger);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(RequestSender::class);
    }

    public function it_sends_requests($logger): void
    {
        $products = $this->getProducts();

        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create();
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $this->beConstructedWith($client, $logger);

        $requestFactory = new RequestFactory();

        $requestMagento = $requestFactory->create(
            'http://172.17.0.1:8000/magentoWebhook',
            json_encode([
                'action' => 'product.updated',
                'event_id' => 'e4593581-0641-4845-9179-fe695d23a0c2',
                'event_date' => '2020-09-02T11:11:11+00:00',
                'data' => $products['productA']
                ]),
            ['secret' => 'magentoSecret']
        );
        $requestErp = $requestFactory->create(
            'http://172.17.0.1:8000/erpWebhook',
            json_encode([
                'action' => 'product.updated',
                'event_id' => 'e4593581-0641-4845-9179-fe695d23a0c3',
                'event_date' => '2020-09-03T22:22:22:+00:00',
                'data' => $products['productB']
            ]),
            ['secret' => 'erpSecret']
        );

        $this->send([$requestMagento,$requestErp]);

        Assert::assertCount(2,$container);
    }

    /**
     * @return array[]
     */
    private function getProducts()
    {
        return [
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
    }
}