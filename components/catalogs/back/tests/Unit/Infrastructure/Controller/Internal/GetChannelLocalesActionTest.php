<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Channel\GetChannelQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetChannelLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetChannelLocalesAction;
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
class GetChannelLocalesActionTest extends TestCase
{
    private ?GetChannelLocalesAction $getChannelLocalesAction;
    private GetChannelQueryInterface&MockObject $getChannelQuery;
    private GetChannelLocalesQueryInterface&MockObject $getChannelLocalesQuery;

    protected function setUp(): void
    {
        $this->getChannelQuery = $this->createMock(GetChannelQueryInterface::class);
        $this->getChannelLocalesQuery = $this->createMock(GetChannelLocalesQueryInterface::class);
        $this->getChannelLocalesAction = new GetChannelLocalesAction($this->getChannelQuery, $this->getChannelLocalesQuery);
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getChannelLocalesAction)(new Request(), 'some_channel_code'));
    }

    public function testItReturnsNotFoundWhenChannelDoesNotExist(): void
    {
        $this->getChannelQuery
            ->method('execute')
            ->with('unknown_channel_code')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        ($this->getChannelLocalesAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'unknown_channel_code',
        );
    }

    public function testItReturnsChannelLocalesFromTheQuery(): void
    {
        $this->getChannelQuery
            ->method('execute')
            ->with('some_channel_code')
            ->willReturn(['label' => 'channelA']);

        $this->getChannelLocalesQuery
            ->method('execute')
            ->with('some_channel_code')
            ->willReturn(['en_US', 'fr_FR']);

        $response = ($this->getChannelLocalesAction)(
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
            \json_encode(['en_US', 'fr_FR'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }
}
