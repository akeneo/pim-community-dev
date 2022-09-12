<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetLocalesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetLocalesQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetLocalesAction;
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
    private ?GetLocalesQueryInterface $getLocalesQuery;
    private ?GetLocalesByCodeQueryInterface $getLocalesByCodeQuery;

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

    public function testItCallsTheGetLocalesQuery(): void
    {
        $this->getLocalesQuery
            ->method('execute')
            ->with()
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
            ->with([])
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

    public function testItAnswersABadRequestIfTheQueryIsInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getLocalesAction)(
            new Request(
                query: ['codes' => ['en_US', 'fr_FR']],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function testItAnswersIfTheQueryIsValid(): void
    {
        $this->expectNotToPerformAssertions();

        ($this->getLocalesAction)(
            new Request(
                query: ['codes' => 'en_US,fr_FR'],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }
}
