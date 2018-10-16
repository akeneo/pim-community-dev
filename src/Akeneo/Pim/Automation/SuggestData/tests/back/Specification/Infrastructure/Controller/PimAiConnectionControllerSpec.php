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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConfigurationQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi\ConnectionStatusNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\PimAiConnectionController;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimAiConnectionControllerSpec extends ObjectBehavior
{
    public function let(
        ActivateConnectionHandler $activateConnectionHandler,
        GetConfigurationHandler $getConfigurationHandler,
        GetConnectionStatusHandler $getConnectionStatusHandler
    ): void {
        $connectionStatusNormalizer = new ConnectionStatusNormalizer();

        $this->beConstructedWith(
            $activateConnectionHandler,
            $getConfigurationHandler,
            $getConnectionStatusHandler,
            $connectionStatusNormalizer
        );
    }

    public function it_is_a_pim_ai_connection_controller(): void
    {
        $this->shouldBeAnInstanceOf(PimAiConnectionController::class);
    }

    public function it_returns_a_response_with_token($getConfigurationHandler): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('foo'));

        $getConfigurationHandler
            ->handle(Argument::type(GetConfigurationQuery::class))
            ->willReturn($configuration);

        $response = $this->getAction(Argument::type(Request::class));
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->isOk()->shouldReturn(true);

        Assert::eq(
            [
                'code' => 'pim-ai',
                'values' => ['token' => 'foo'],
            ],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_returns_a_response_without_token($getConfigurationHandler): void
    {
        $configuration = new Configuration();

        $getConfigurationHandler
            ->handle(Argument::type(GetConfigurationQuery::class))
            ->willReturn($configuration);

        $response = $this->getAction(Argument::type(Request::class));
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->isOk()->shouldReturn(true);

        Assert::eq(
            [
                'code' => 'pim-ai',
                'values' => ['token' => null],
            ],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_returns_the_connection_status($getConnectionStatusHandler): void
    {
        $connectionStatus = new ConnectionStatus(true);
        $getConnectionStatusHandler
            ->handle(Argument::type(GetConnectionStatusQuery::class))
            ->willReturn($connectionStatus);

        $response = $this->isActiveAction(Argument::type(Request::class));
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->isOk()->shouldReturn(true);

        Assert::eq(
            ['is_active' => true],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_activates_connection_and_returns_a_success_message(
        $activateConnectionHandler,
        Request $request
    ): void {
        $jsonContent = $this->loadFakeData('post');
        $request->getContent()->willReturn($jsonContent);

        $activateConnectionHandler->handle(Argument::type(ActivateConnectionCommand::class))->shouldBeCalled();

        $response = $this->postAction($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->isOk()->shouldReturn(true);

        Assert::eq(
            ['message' => 'akeneo_suggest_data.connection.flash.success'],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_returns_an_unprocessable_entity_response_with_invalid_token_message_on_activation_fail(
        $activateConnectionHandler,
        Request $request
    ): void {
        $jsonContent = $this->loadFakeData('post');
        $request->getContent()->willReturn($jsonContent);

        $activateConnectionHandler
            ->handle(Argument::type(ActivateConnectionCommand::class))
            ->willThrow(ConnectionConfigurationException::invalidToken());

        $response = $this->postAction($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getStatusCode()->shouldReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        Assert::eq(
            ['message' => 'akeneo_suggest_data.connection.flash.invalid'],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_returns_an_unprocessable_entity_response_when_activation_request_format_is_incorrect(
        $activateConnectionHandler,
        Request $request
    ): void {
        $request->getContent()->willReturn('');

        $activateConnectionHandler->handle(Argument::type(ActivateConnectionCommand::class))->shouldNotBeCalled();

        $response = $this->postAction($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getStatusCode()->shouldReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        Assert::eq(
            ['message' => 'akeneo_suggest_data.connection.flash.error'],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    public function it_returns_an_unprocessable_entity_response_when_activation_token_format_is_incorrect(
        $activateConnectionHandler,
        Request $request
    ): void {
        $jsonContent = $this->loadFakeData('post');
        $jsonContent = str_replace('my-token', '', $jsonContent);
        $request->getContent()->willReturn($jsonContent);

        $activateConnectionHandler->handle(Argument::type(ActivateConnectionCommand::class))->shouldNotBeCalled();

        $response = $this->postAction($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getStatusCode()->shouldReturn(Response::HTTP_UNPROCESSABLE_ENTITY);

        Assert::eq(
            ['message' => 'akeneo_suggest_data.connection.flash.error'],
            json_decode($response->getContent()->getWrappedObject(), true)
        );
    }

    /**
     * Loads fake json content from a file.
     *
     * @param string $filename
     *
     * @return string
     */
    private function loadFakeData($filename): string
    {
        $directory = realpath(
            sprintf('%s/../../../%s/%s', __DIR__, 'Resources/fake/pim-internal-api', 'pim-ai-connection')
        );
        $filepath = sprintf('%s/%s.json', $directory, $filename);
        if (!file_exists($filepath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found', $filepath));
        }

        return file_get_contents($filepath);
    }
}
