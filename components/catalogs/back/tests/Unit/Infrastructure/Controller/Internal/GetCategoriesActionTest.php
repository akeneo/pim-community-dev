<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryTreeRootsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoriesAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoriesAction
 */
class GetCategoriesActionTest extends TestCase
{
    private ?GetCategoriesAction $getCategoriesAction;
    private GetCategoryTreeRootsQueryInterface&MockObject $getCategoryTreeRootsQuery;
    private GetCategoriesByCodeQueryInterface&MockObject $getCategoriesByCodeQuery;

    protected function setUp(): void
    {
        $this->getCategoryTreeRootsQuery = $this->createMock(GetCategoryTreeRootsQueryInterface::class);
        $this->getCategoriesByCodeQuery = $this->createMock(GetCategoriesByCodeQueryInterface::class);
        $this->getCategoriesAction = new GetCategoriesAction(
            $this->getCategoryTreeRootsQuery,
            $this->getCategoriesByCodeQuery,
        );
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getCategoriesAction)(new Request()));
    }

    /**
     * @dataProvider queryWillThrowDataProvider
     */
    public function testItAnswersABadRequestIfTheQueryIsInvalid(array $queryPayload): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getCategoriesAction)(
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
            'must specify either codes or is_root' => [
                [],
            ],
            'codes must be a string' => [
                [
                    'codes' => 456,
                ],
            ],
            'locale must be a string' => [
                [
                    'codes' => 'a,b,c',
                    'locale' => 456,
                ],
            ],
            'codes and is_root are mutually exclusive' => [
                [
                    'codes' => 'a,b,c',
                    'is_root' => true,
                ],
            ],
        ];
    }

    public function testItReturnsCategoriesFromTheQuery(): void
    {
        $this->getCategoriesByCodeQuery
            ->method('execute')
            ->with(['codeA', 'codeB', 'codeC'], 'fr_FR')
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoriesAction)(
            new Request(
                query: ['codes' => 'codeA,codeB,codeC', 'locale' => 'fr_FR'],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }

    public function testItReturnsRootCategoriesFromTheQuery(): void
    {
        $this->getCategoryTreeRootsQuery
            ->method('execute')
            ->with('en_US')
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoriesAction)(
            new Request(
                query: ['is_root' => true],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }
}
