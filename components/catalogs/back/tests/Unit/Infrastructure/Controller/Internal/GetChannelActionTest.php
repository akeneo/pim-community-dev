<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetChannelAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelActionTest extends TestCase
{
    private ?GetChannelAction $getChannelAction;
    private GetChannelQueryInterface&MockObject $getChannelQuery;

    protected function setUp(): void
    {
        $this->getChannelQuery = $this->createMock(GetChannelQueryInterface::class);
        $this->getChannelAction = new GetChannelAction($this->getChannelQuery);
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getChannelAction)(new Request(), 'some_channel_code'));
    }

    public function testItReturnsNotFoundWhenChannelDoesNotExist(): void
    {
        $this->getChannelQuery
            ->method('execute')
            ->with('unknown_channel_code')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        ($this->getChannelAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'unknown_channel_code',
        );
    }

    public function testItReturnsChannelFromTheQuery(): void
    {
        $this->getChannelQuery
            ->method('execute')
            ->with('some_channel_code')
            ->willReturn(['label' => 'channelA']);

        $response = ($this->getChannelAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'some_channel_code',
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['label' => 'channelA'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }
}
