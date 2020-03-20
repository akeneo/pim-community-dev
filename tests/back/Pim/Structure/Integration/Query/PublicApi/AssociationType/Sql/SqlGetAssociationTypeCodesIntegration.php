<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Query\PublicApi\AssociationType\Sql;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AssociationType\Sql\SqlGetAssociationTypeCodes;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAssociationTypeCodesIntegration extends TestCase
{
    /** @var SqlGetAssociationTypeCodes */
    private $sqlGetAssociationTypeCodes;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAssociationTypeCodes = $this->get('akeneo.pim.structure.query.sql_get_association_type_codes');

        $associationTypes = array_map(function (string $code): AssociationType {
            $associationType = new AssociationType();
            $associationType->setCode($code);

            return $associationType;
        }, $this->createAssociationTypeCodes());

        $this->get('pim_catalog.saver.association_type')->saveAll($associationTypes);
    }

    public function test_it_returns_all_association_type_codes(): void
    {
        $results = iterator_to_array($this->sqlGetAssociationTypeCodes->findAll());
        sort($results);

        $expected = array_merge(
            ['PACK', 'X_SELL', 'SUBSTITUTION', 'UPSELL'], // already in minimal catalog
            $this->createAssociationTypeCodes(),
        );
        sort($expected);

        $this->assertEquals($expected, $results);
    }

    /**
     * @param int $number
     * @return string[]
     */
    private function createAssociationTypeCodes(int $number = 1000): array
    {
        return array_map(function (string $index): string {
            return sprintf('code_%d', $index);
        }, range(1, $number));
    }
}
