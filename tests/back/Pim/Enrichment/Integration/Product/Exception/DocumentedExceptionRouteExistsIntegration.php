<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Routing\RouterInterface;

class DocumentedExceptionRouteExistsIntegration extends TestCase
{
    public function test_that_attribute_unknown_exception_documents_an_existing_route()
    {
        $exception = UnknownAttributeException::unknownAttribute('description');
        $this->assertRoutesExist($exception->getDocumentation(), UnknownAttributeException::class);
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
