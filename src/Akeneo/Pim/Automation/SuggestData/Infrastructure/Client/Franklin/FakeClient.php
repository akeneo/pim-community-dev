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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeClient implements ClientInterface
{
    /** @const string */
    public const VALID_TOKEN = 'valid-token';

    /** @const string */
    public const INVALID_TOKEN = 'invalid-token';

    /** @const string */
    public const FAKE_PATH = __DIR__ . '/../../../tests/back/Resources/fake/franklin-api/';

    /** @var string */
    private $token;

    /** @var bool */
    private $hasCredits = true;

    /** @var bool */
    private $serverIsDown = false;

    /** @var string */
    private $lastFetchDate;

    /** @var array */
    private $identifiersMapping = [];

    /** @var array */
    private $attributesMapping = [];

    /** @var array */
    private $optionsMapping = [];

    /** @var UriGenerator */
    private $uriGenerator;

    /**
     * @param UriGenerator $uriGenerator
     */
    public function __construct(UriGenerator $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        // Remove base uri
        $uri = str_replace($this->uriGenerator->getBaseUri() . '/api/', '', $uri);

        // Simulates that Franklin server is down
        if ($this->serverIsDown) {
            return new \GuzzleHttp\Psr7\Response(Response::HTTP_GATEWAY_TIMEOUT);
        }

        if ('stats' === $uri) {
            return $this->authenticate($method, $uri, $options);
        }

        $this->handleToken($method, $uri, $options);

        $this->handleCredits($method, $uri);

        if (false !== strpos($uri, 'mapping')) {
            return $this->handleMapping($method, $uri, $options);
        }

        if (false !== strpos($uri, 'subscriptions')) {
            return $this->handleSubscription($method, $uri, $options);
        }

        throw new \LogicException('Request has not been catched by the Fake Client');
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getIdentifiersMapping(): array
    {
        return $this->identifiersMapping;
    }

    /**
     * @return array
     */
    public function getOptionsMapping(): array
    {
        return $this->optionsMapping;
    }

    /**
     * Disable user credits.
     */
    public function disableCredit(): void
    {
        $this->hasCredits = false;
    }

    /**
     * @param string $lastFetchDate
     */
    public function defineLastFetchDate(string $lastFetchDate): void
    {
        $this->lastFetchDate = $lastFetchDate;
    }

    /**
     * Define the server as down.
     */
    public function makeTheServerDown(): void
    {
        $this->serverIsDown = true;
    }

    /**
     * Try to authenticate to Franklin with a defined token.
     *
     * Returns a response with HTTP 200 if token is valid
     * Throws a client exception with invalid token otherwise
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws ClientException
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function authenticate(string $method, string $uri, array $options): \GuzzleHttp\Psr7\Response
    {
        if (self::VALID_TOKEN !== $options['headers']['Authorization']) {
            $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_FORBIDDEN);
            throw new ClientException('Invalid token', new Request($method, $uri), $response);
        }

        return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
    }

    /**
     * If token is invalid, raise a client exception.
     *
     * @param string $method
     * @param string $uri
     *
     * @throws ClientException
     */
    private function handleToken(string $method, string $uri): void
    {
        if (self::INVALID_TOKEN === $this->token) {
            $request = new Request($method, $uri);
            $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_FORBIDDEN);
            throw new ClientException('Invalid token', $request, $response);
        }
    }

    /**
     * Throw an exception if the user does not have credit.
     *
     * @param string $method
     * @param string $uri
     *
     * @throws ClientException
     */
    private function handleCredits(string $method, string $uri): void
    {
        if (!$this->hasCredits) {
            $request = new Request($method, $uri);
            $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_PAYMENT_REQUIRED);
            throw new ClientException('Invalid token', $request, $response);
        }
    }

    /**
     * Fake mapping end-points.
     * When getting mapping, it returns a fake data content from a file in the fake path
     * When saving mapping, it saves it into a dedicated instance variable that could be assert through a getter.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws \LogicException
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function handleMapping(string $method, string $uri, array $options): \GuzzleHttp\Psr7\Response
    {
        if ('GET' === $method) {
            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $this->loadFakeData($uri));
        }

        // Deal with PUT/POST
        // TODO: Assert sent data
        if (false !== strpos($uri, 'identifiers')) {
            $this->identifiersMapping = $options['form_params'];
        } elseif (false !== strpos($uri, 'options')) {
            $this->optionsMapping = $options['form_params'];
        } elseif (false !== strpos($uri, 'attributes') && false === strpos($uri, 'options')) {
            $this->attributesMapping = $options['form_params'];
        } else {
            throw new \LogicException('Something went wrong when trying to save mapping');
        }

        return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
    }

    /**
     * Handles subscription end-points.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @throws \LogicException
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function handleSubscription(string $method, string $uri, array $options): \GuzzleHttp\Psr7\Response
    {
        switch ($method) {
            case 'DELETE':
                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, []);
            case 'GET':
                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $this->loadFakeData($uri));
            case 'POST':
                // TODO: Assert sent parameters
                if (isset($options['form_params'][0]['asin'])) {
                    $key = 'asin';
                } elseif (isset($options['form_params'][0]['upc'])) {
                    $key = 'upc';
                } elseif (isset($options['form_params'][0]['mpn']) && isset($options['form_params'][0]['brand'])) {
                    $key = 'mpn-brand';
                } else {
                    throw new \LogicException('Parameters sent for subscription are incorrects');
                }

                $jsonContent = $this->loadFakeData(sprintf('%s/%s-%s', $uri, $key, $options['form_params'][0][$key]));

                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
            case 'PUT':
                // TODO check form params
                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, []);
            default:
                throw new \LogicException('Wrong method send for subscription');
        }
    }

    /**
     * @param string $filepath
     *
     * @throws \LogicException
     *
     * @return string
     */
    private function loadFakeData(string $filepath): string
    {
        $fakeFilepath = sprintf('%s/%s.json', realpath(self::FAKE_PATH), $filepath);
        if (!is_file($fakeFilepath)) {
            throw new \LogicException(
                sprintf('File "%s" not found. The FakeClient cannot provide you fake data.', $fakeFilepath)
            );
        }

        return file_get_contents($fakeFilepath);
    }
}
