<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryChildrenQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoryChildrenAction;
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
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoryChildrenAction
 */
class GetCategoryChildrenActionTest extends TestCase
{
    private ?GetCategoryChildrenAction $getCategoryChildrenAction;
    private GetCategoryChildrenQueryInterface&MockObject $getCategoryChildrenQuery;

    protected function setUp(): void
    {
        $this->getCategoryChildrenQuery = $this->createMock(GetCategoryChildrenQueryInterface::class);
        $this->getCategoryChildrenAction = new GetCategoryChildrenAction(
            $this->getCategoryChildrenQuery,
        );
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getCategoryChildrenAction)(new Request(), 'master'));
    }

    public function testItReturnsCategoryChildrenFromTheQuery(): void
    {
        $this->getCategoryChildrenQuery
            ->method('execute')
            ->with('master', 'en_US')
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoryChildrenAction)(
            new Request(
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'master',
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }

    public function testItReturnsCategoryChildrenFromTheQueryUsingLocaleFromTheQueryPayload(): void
    {
        $this->getCategoryChildrenQuery
            ->method('execute')
            ->with('master', 'de_DE')
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoryChildrenAction)(
            new Request(
                query: ['locale' => 'de_DE'],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'master',
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent(),
        );
    }

    public function testItAnswersABadRequestIfTheLocaleIsInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getCategoryChildrenAction)(
            new Request(
                query: ['locale' => 123],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'master',
        );
    }
}
