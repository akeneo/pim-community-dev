<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\WebhookBundle\Client;

use Akeneo\Tool\Bundle\WebhookBundle\Client\RequestFactory;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestFactorySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RequestFactory::class);
    }

    public function it_creates_a_request(): void
    {
        $wrapper = $this->create(
            'http://localhost',
            '{"data":"Hello world!"}',
            [
                'secret' => '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b',
                'headers' => [
                    'X-Custom-Header' => 'My custom header!'
                ]
            ]
        );

        $wrapper->shouldHaveType(Request::class);

        /** @var Request */
        $request = $wrapper->getWrappedObject();

        Assert::assertEquals($request->getMethod(), 'POST');
        Assert::assertEquals((string) $request->getUri(), 'http://localhost');
        Assert::assertEquals($request->getBody(), '{"data":"Hello world!"}');

        Assert::assertEquals($request->getHeader('Content-Type'), ['application/json']);
        Assert::assertEquals($request->getHeader('X-Custom-Header'), ['My custom header!']);

        // Signature Headers
        Assert::assertNotEmpty($request->getHeader('X-Akeneo-Signature'));
        Assert::assertNotEmpty($request->getHeader('X-Akeneo-Signature-Timestamp'));
    }

    public function it_requires_a_secret_to_create_a_request(): void
    {
        $this->shouldThrow(new \InvalidArgumentException('The "secret" option is missing.'))
            ->during('create', ['http://localhost', '{"data":"Hello world!"}', []]);
    }
}
