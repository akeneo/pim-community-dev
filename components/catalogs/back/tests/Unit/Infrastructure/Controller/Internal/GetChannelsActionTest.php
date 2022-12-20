<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelsByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetChannelsAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    private GetChannelsQueryInterface&MockObject $getChannelsQuery;
    private GetChannelsByCodeQueryInterface&MockObject $getChannelsByCodeQuery;

    protected function setUp(): void
    {
        $this->getChannelsQuery = $this->createMock(GetChannelsQueryInterface::class);
        $this->getChannelsByCodeQuery = $this->createMock(GetChannelsByCodeQueryInterface::class);
        $this->getChannelsAction = new GetChannelsAction($this->getChannelsQuery, $this->getChannelsByCodeQuery);
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getChannelsAction)(new Request()));
    }

    public function testItCallsTheGetChannelsQueryWithDefaultValues(): void
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

        self::assertSame(['channelA', 'channelB', 'channelC'], \json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR));
    }

    public function testItReturnsNoChannelsWhenNoCodesAreProvided(): void
    {
        $this->getChannelsByCodeQuery
            ->method('execute')
            ->with([], 1, 20)
            ->willReturn([]);

        $response = ($this->getChannelsAction)(
            new Request(
                query: [
                    'codes' => '',
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertSame([], \json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR));
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
            'codes is not a string' => [
                [
                    'codes' => ['ecommerce', 'print'],
                ],
            ],
        ];
    }


    /**
     * @dataProvider queryWillNotThrowDataProvider
     */
    public function testItAnswersIfTheQueryIsValid(array $query): void
    {
        $this->expectNotToPerformAssertions();

        ($this->getChannelsAction)(
            new Request(
                query: $query,
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function queryWillNotThrowDataProvider(): array
    {
        return [
            'limit and page are positive' => [
                [
                    'page' => 1,
                    'limit' => 1,
                    'codes' => 'ecommerce,print',
                ],
            ],
        ];
    }
}
