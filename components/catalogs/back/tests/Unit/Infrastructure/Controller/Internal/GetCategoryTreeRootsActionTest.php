<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetCategoryTreeRootsQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoryTreeRootsAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsActionTest extends TestCase
{
    private ?GetCategoryTreeRootsAction $getCategoryTreeRootsAction;
    private ?GetCategoryTreeRootsQueryInterface $getCategoryTreeRootsQuery;

    protected function setUp(): void
    {
        $this->getCategoryTreeRootsQuery = $this->createMock(GetCategoryTreeRootsQueryInterface::class);
        $this->getCategoryTreeRootsAction = new GetCategoryTreeRootsAction(
            $this->getCategoryTreeRootsQuery,
        );
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getCategoryTreeRootsAction)(new Request()));
    }

    public function testItReturnsCategoryTreeRootsFromTheQuery(): void
    {
        $this->getCategoryTreeRootsQuery
            ->method('execute')
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoryTreeRootsAction)(
            new Request(
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            )
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }
}
