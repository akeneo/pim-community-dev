<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetLocalesAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetLocalesActionTest extends TestCase
{
    private ?GetLocalesAction $getLocalesAction;
    private GetLocalesQueryInterface&MockObject $getLocalesQuery;
    private GetLocalesByCodeQueryInterface&MockObject $getLocalesByCodeQuery;

    protected function setUp(): void
    {
        $this->getLocalesQuery = $this->createMock(GetLocalesQueryInterface::class);
        $this->getLocalesByCodeQuery = $this->createMock(GetLocalesByCodeQueryInterface::class);
        $this->getLocalesAction = new GetLocalesAction($this->getLocalesQuery, $this->getLocalesByCodeQuery);
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getLocalesAction)(new Request()));
    }

    public function testItCallsTheGetLocalesQueryWithDefaultValues(): void
    {
        $this->getLocalesQuery
            ->method('execute')
            ->with(1, 20)
            ->willReturn(['en_EN', 'fr_FR', 'de_DE']);

        $response = ($this->getLocalesAction)(
            new Request(
                query: [],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertSame(['en_EN', 'fr_FR', 'de_DE'], \json_decode($response->getContent(), null, 512, JSON_THROW_ON_ERROR));
    }

    public function testItReturnsNoLocalesWhenNoCodesAreProvided(): void
    {
        $this->getLocalesByCodeQuery
            ->method('execute')
            ->with([], 1, 20)
            ->willReturn([]);

        $response = ($this->getLocalesAction)(
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

        ($this->getLocalesAction)(
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
                    'codes' => ['en_US', 'fr_FR'],
                ],
            ],
        ];
    }

    public function testItAnswersWhenLimitAndPageArePositive(): void
    {
        $this->expectNotToPerformAssertions();

        ($this->getLocalesAction)(
            new Request(
                query: [
                    'page' => 1,
                    'limit' => 1,
                    'codes' => 'en_US,fr_FR',
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }
}
