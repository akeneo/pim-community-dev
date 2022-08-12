<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetCategoryChildrenQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetCategoryChildrenAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryChildrenActionTest extends TestCase
{
    private ?GetCategoryChildrenAction $getCategoryChildrenAction;
    private ?GetCategoryChildrenQueryInterface $getCategoryChildrenQuery;

    protected function setUp(): void
    {
        $this->getCategoryChildrenQuery = $this->createMock(GetCategoryChildrenQueryInterface::class);
        $this->getCategoryChildrenAction = new GetCategoryChildrenAction(
            $this->getCategoryChildrenQuery,
        );
    }

    public function testItRedirectsWhenRequestIsNotAXmlHttpRequest(): void
    {
        self::assertInstanceOf(RedirectResponse::class, ($this->getCategoryChildrenAction)(new Request(), 12));
    }

    public function testItReturnsCategoryChildrenFromTheQuery(): void
    {
        $this->getCategoryChildrenQuery
            ->method('execute')
            ->with(12)
            ->willReturn(['categoryA', 'categoryB', 'categoryC']);

        $response = ($this->getCategoryChildrenAction)(
            new Request(
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
            12
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            \json_encode(['categoryA', 'categoryB', 'categoryC'], JSON_THROW_ON_ERROR),
            $response->getContent()
        );
    }
}
