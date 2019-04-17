<?php


declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\ProductModel\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductAssociationsByProductModelCodes;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductAssociationsByProductModelCodesIntegration extends TestCase
{

    public function it_returns_empty_for_product_model_with_no_associations()
    {

    }

    public function it_returns_associations_for_a_single_product_model()
    {

    }

    public function it_returns_associations_for_multiple_product_models()
    {

    }

    public function it_returns_inherited_associations_of_product_models()
    {

    }

    public function setUp(): void
    {

    }

    private function getQuery(): GetProductAssociationsByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_product_associations_by_product_model_codes');
    }

    protected function getConfiguration()
    {
        $this->catalog->useMinimalCatalog();
    }
}
