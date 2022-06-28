<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetFamiliesAction;
use Akeneo\Catalogs\Infrastructure\Persistence\SearchFamilyQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetFamiliesActionTest extends TestCase
{
    private ?GetFamiliesAction $getFamiliesAction;
    private ?SearchFamilyQuery $searchFamilyQuery;

    protected function setUp(): void
    {
        $this->searchFamilyQuery = $this->createMock(SearchFamilyQuery::class);
        $this->getFamiliesAction = new GetFamiliesAction($this->searchFamilyQuery);
    }

    public function testItRedirectsIfTheRequestIsNotAnXMLHTTPRequest(): void
    {
        $this->assertInstanceOf(
            RedirectResponse::class,
            ($this->getFamiliesAction)(new Request())
        );
    }

    /**
     * @dataProvider query
     */
    public function testItAnswersABadRequestIfTheQueryIsInvalid(array $query, string $message): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage($message);

        ($this->getFamiliesAction)(new Request(
            query: $query,
            server: [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        ));
    }

    public function query(): array
    {
        return [
            'page must be a numeric' => [
                [
                    'page' => 'foo',
                ],
                'Page and limit must be a number.',
            ],
            'limit must be a numeric' => [
                [
                    'limit' => 'foo',
                ],
                'Page and limit must be a number.',
            ],
            'page must be positive' => [
                [
                    'page' => -2,
                ],
                'Page and limit must be positive.',
            ],
            'limit must be positive' => [
                [
                    'limit' => '-2',
                ],
                'Page and limit must be positive.',
            ],
            'search must be string or null' => [
                [
                    'search' => 42,
                ],
                'Search must must be a string or null.',
            ],
        ];
    }
}
