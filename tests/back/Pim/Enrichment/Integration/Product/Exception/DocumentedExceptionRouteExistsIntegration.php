<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Exception;

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

    private function assertRoutesExist(array $documentation, string $fqcn): void
    {
        /** @var RouterInterface */
        $router = $this->get('router');
        $collection = $router->getRouteCollection();

        $routesNotFound = [];
        foreach ($documentation as $document) {
            foreach ($document['params'] as $param) {
                if ('route' === $param['type'] && null === $collection->get($param['route'])) {
                    $routesNotFound[] = $param['route'];
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
