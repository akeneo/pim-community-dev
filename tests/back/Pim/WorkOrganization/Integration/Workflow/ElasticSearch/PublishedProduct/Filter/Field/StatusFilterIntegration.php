<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\PublishedProduct\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StatusFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function testOperatorEquals(): void
    {
        $result = $this->executeFilter([['enabled', Operators::EQUALS, true]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['enabled', Operators::EQUALS, false]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorNotEqual(): void
    {
        $result = $this->executeFilter([['enabled', Operators::NOT_EQUAL, true]]);
        $this->assert($result, ['bar']);

        $result = $this->executeFilter([['enabled', Operators::NOT_EQUAL, false]]);
        $this->assert($result, ['foo']);
    }

    public function testErrorDataIsMalformed(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "enabled" expects a boolean as data, "string" given.');

        $this->executeFilter([['enabled', Operators::EQUALS, 'string']]);
    }

    public function testErrorOperatorNotSupported(): void
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage(
            'Filter on property "enabled" is not supported or does not support operator "BETWEEN"'
        );

        $this->executeFilter([['enabled', Operators::BETWEEN, false]]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->esProductClient = $this->get('akeneo_elasticsearch.client.published_product');
        $publishedProductManager = $this->get('pimee_workflow.manager.published_product');

        $foo = $this->createProduct('foo', ['enabled' => true]);
        $publishedProductManager->publish($foo);
        $bar = $this->createProduct('bar', ['enabled' => false]);
        $publishedProductManager->publish($bar);

        $this->esProductClient->refreshIndex();
    }

    protected function executeFilter(array $filters): CursorInterface
    {
        $pqb = $this->get('pimee_workflow.doctrine.query.published_product_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $pqb->addFilter($filter[0], $filter[1], $filter[2]);
        }

        return $pqb->execute();
    }
}
