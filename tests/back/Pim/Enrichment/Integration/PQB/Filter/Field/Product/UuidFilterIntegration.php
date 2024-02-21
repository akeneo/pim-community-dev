<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Field\Product;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UuidFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    private array $uuids = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['foo', 'bar', 'baz'] as $identifier) {
            $this->uuids[$identifier] = $this->createProduct($identifier, [])->getUuid()->toString();
        }
    }

    public function testOperatorInList(): void
    {
        $result = $this->executeFilter([['uuid', Operators::IN_LIST, [$this->uuids['foo'], $this->uuids['baz']]]]);
        $this->assert($result, ['foo', 'baz']);
    }

    public function testOperatorNotInList(): void
    {
        $result = $this->executeFilter([['uuid', Operators::NOT_IN_LIST, [$this->uuids['foo'], $this->uuids['baz']]]]);
        $this->assert($result, ['bar']);
    }

    public function testEmptyList(): void
    {
        $result = $this->executeFilter([['uuid', Operators::IN_LIST, []]]);
        $this->assert($result, []);
    }

    public function testInvalidOperator(): void
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->executeFilter([['uuid', '=', ['toto']]]);
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->executeFilter([['uuid', 'IN', 'abc']]);
    }

    public function testInvalidArrayItemValue(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->executeFilter([['uuid', 'IN', [123]]]);
    }

    public function testCaseInsensitiveUuid(): void
    {
        $upperCaseUuids = [\mb_strtoupper($this->uuids['foo']), \mb_strtoupper($this->uuids['baz'])];

        $this->assert(
            $this->executeFilter([['uuid', Operators::IN_LIST, $upperCaseUuids]]),
            ['foo', 'baz'],
        );
        $this->assert(
            $this->executeFilter([['uuid', Operators::NOT_IN_LIST, $upperCaseUuids]]),
            ['bar'],
        );
    }
}
