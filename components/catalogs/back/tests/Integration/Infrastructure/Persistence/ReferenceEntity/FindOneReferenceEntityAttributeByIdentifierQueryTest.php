<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\ReferenceEntity;

use Akeneo\Catalogs\Infrastructure\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\ReferenceEntity\FindOneReferenceEntityAttributeByIdentifierQuery
 */
class FindOneReferenceEntityAttributeByIdentifierQueryTest extends IntegrationTestCase
{
    private ?FindOneReferenceEntityAttributeByIdentifierQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(FindOneReferenceEntityAttributeByIdentifierQuery::class);
    }

    /**
     * @group ce
     */
    public function testItReturnsNull(): void
    {
        $result = $this->query->execute('name');

        $this->assertNull($result);
    }
}
