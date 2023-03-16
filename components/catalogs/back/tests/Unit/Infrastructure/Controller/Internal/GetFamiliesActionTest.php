<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamiliesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Family\SearchFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetFamiliesAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetFamiliesActionTest extends TestCase
{
    private ?GetFamiliesAction $getFamiliesAction;
    private SearchFamilyQueryInterface&MockObject $searchFamilyQuery;
    private GetFamiliesByCodeQueryInterface&MockObject $getFamiliesByCodeQuery;

    protected function setUp(): void
    {
        $this->searchFamilyQuery = $this->createMock(SearchFamilyQueryInterface::class);
        $this->getFamiliesByCodeQuery = $this->createMock(GetFamiliesByCodeQueryInterface::class);
        $this->getFamiliesAction = new GetFamiliesAction(
            $this->searchFamilyQuery,
            $this->getFamiliesByCodeQuery,
        );
    }

    public function testItCallsTheSearchQueryWhenCodesIsEmpty(): void
    {
        $this->searchFamilyQuery->expects($this->once())
            ->method('execute')
            ->with(null, 1, 20);

        ($this->getFamiliesAction)(
            new Request(
                query: [
                    'codes' => ' ',
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function testItCallsTheGetFamiliesQueryWithDefaultValues(): void
    {
        $this->getFamiliesByCodeQuery->expects($this->once())
            ->method('execute')
            ->with(['foo', 'bar'], 1, 20);

        ($this->getFamiliesAction)(
            new Request(
                query: [
                    'codes' => 'foo,bar',
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function testItRedirectsIfTheRequestIsNotAnXMLHTTPRequest(): void
    {
        $this->assertInstanceOf(
            RedirectResponse::class,
            ($this->getFamiliesAction)(new Request()),
        );
    }

    /**
     * @dataProvider queryWillThrow
     */
    public function testItAnswersABadRequestIfTheQueryIsInvalid(array $query): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getFamiliesAction)(
            new Request(
                query: $query,
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function queryWillThrow(): array
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
            'search must be a string' => [
                [
                    'search' => 42,
                ],
            ],
            'codes must be a string' => [
                [
                    'codes' => 42,
                ],
            ],
        ];
    }

    /**
     * @dataProvider queryWillNotThrow
     */
    public function testItAnswersIfTheQueryIsValid(array $query): void
    {
        $this->expectNotToPerformAssertions();

        ($this->getFamiliesAction)(
            new Request(
                query: $query,
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );
    }

    public function queryWillNotThrow(): array
    {
        return [
            'limit and page are positive' => [
                [
                    'page' => 1,
                    'limit' => 1,
                ],
            ],
            'search is string' => [
                [
                    'search' => 'foo',
                ],
            ],
            'search is null' => [
                [
                    'search' => null,
                ],
            ],
            'codes is string' => [
                [
                    'codes' => 'foo',
                ],
            ],
            'codes is null' => [
                [
                    'codes' => null,
                ],
            ],
        ];
    }
}
