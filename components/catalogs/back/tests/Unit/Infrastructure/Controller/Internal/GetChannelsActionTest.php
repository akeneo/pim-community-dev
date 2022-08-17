<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetChannelsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetChannelsAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelsActionTest extends TestCase
{
    private ?GetChannelsAction $getChannelsAction;
    private ?GetChannelsQueryInterface $getChannelsQuery;

    public function setUp(): void
    {
        $this->getChannelsQuery = $this->createMock(GetChannelsQueryInterface::class);
        $this->getChannelsAction = new GetChannelsAction($this->getChannelsQuery);
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getChannelsAction)(new Request()));
    }

    /**
     * @dataProvider queryWillThrowDataProvider
     */
    public function testItAnswersABadRequestIfTheQueryIsInvalid(array $queryPayload): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getChannelsAction)(
            new Request(
                query: $queryPayload,
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function queryWillThrowDataProvider(): array
    {
        return [
            'page must be a numeric' => [
                [
                    'page' => 'foo',
                ],
            ],
            'limit must be a numeric' => [
                [
                    'limit' => 'foo',
                ],
            ],
            'page must be positive' => [
                [
                    'page' => 0,
                ],
            ],
            'limit must be positive' => [
                [
                    'limit' => 0,
                ],
            ],
        ];
    }

    public function testItReturnsChannelsFromTheQuery(): void
    {
        $this->getChannelsQuery
            ->method('execute')
            ->with(1, 20)
            ->willReturn(['channelA', 'channelB', 'channelC']);

        $response = ($this->getChannelsAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['channelA', 'channelB', 'channelC'], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }
}
