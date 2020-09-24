<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Mock;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MockGuzzleHttpClient extends Client
{
    /** @var string */
    const RESPONSE_200 = 'www.get-response-200.com';

    /** @var string */
    const RESPONSE_451 = 'www.get-response-451.com';

    /** @var string */
    const RESPONSE_500 = 'www.get-response-500.com';

    /** @var array<string,Response|RequestException> */
    private $mockResponses;

    /**
     * @param array{mixed: mixed}[] $config
     */
    public function __construct(array $config = [])
    {
        $this->mockResponses = $this->buildMockResponses();

        parent::__construct($config);
    }

    /**
     * @param RequestInterface $request
     * @param array{mixed,mixed}[] $options
     * @return mixed|\Psr\Http\Message\ResponseInterface|void|null
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $url = $request->getUri()->getHost();

        $mockResponse = !array_key_exists(
            $url,
            $this->mockResponses
        ) ? $this->mockResponses[self::RESPONSE_500] : $this->mockResponses[$url];

        if ($mockResponse instanceof RequestException) {
            throw $mockResponse;
        }

        return $mockResponse;
    }

    /**
     * @return array<string,Response|RequestException>
     */
    private function buildMockResponses(): array
    {
        return [
            self::RESPONSE_200 => new Response(200, [], null, '1.1', 'OK'),
            self::RESPONSE_451 => new RequestException(
                'RequestException Message',
                new Request('POST', self::RESPONSE_451),
                new Response(451, [], null, '1.1', 'Unavailable For Legal Reasons')
            ),
            self::RESPONSE_500 => new RequestException(
                'Failed to connect to server',
                new Request('POST', self::RESPONSE_500),
            ),
        ];
    }
}
