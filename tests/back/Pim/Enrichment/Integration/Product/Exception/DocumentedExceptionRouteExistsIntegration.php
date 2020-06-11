<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownFamilyException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Routing\RouterInterface;

class DocumentedExceptionRouteExistsIntegration extends TestCase
{
    public function test_that_unknown_attribute_exception_documents_an_existing_route()
    {
        $exception = new UnknownAttributeException('description');
        $this->assertRoutesExist($exception->getDocumentation(), UnknownAttributeException::class);
    }

    public function test_that_unknown_category_exception_documents_an_existing_route()
    {
        $exception = new UnknownCategoryException('category', 'category_code', self::class);
        $this->assertRoutesExist($exception->getDocumentation(), UnknownCategoryException::class);
    }

    public function test_that_unknown_family_exception_documents_an_existing_route()
    {
        $exception = new UnknownFamilyException('family', 'family_code', self::class);
        $this->assertRoutesExist($exception->getDocumentation(), UnknownFamilyException::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertRoutesExist(DocumentationCollection $documentationCollection, string $fqcn): void
    {
        /** @var RouterInterface */
        $router = $this->get('router');
        $routeCollection = $router->getRouteCollection();

        $routesNotFound = [];
        foreach ($documentationCollection->normalize() as $documentation) {
            foreach ($documentation['parameters'] as $param) {
                if (
                    MessageParameterTypes::ROUTE === $param['type'] &&
                    null === $routeCollection->get($param[MessageParameterTypes::ROUTE])
                ) {
                    $routesNotFound[] = $param[MessageParameterTypes::ROUTE];
                }
            }
        }

        Assert::assertCount(
            0,
            $routesNotFound,
            sprintf(
                'The documented routes "%s" from "%s" were not found in the routes definition.',
                implode(', ', $routesNotFound),
                $fqcn
            )
        );
    }
}
