<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Completeness\SwitchVariantProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchVariantProductCompletenessRatioSpec extends ObjectBehavior
{
    function let(
        VariantProductRatioInterface $legacyVariantProductRatio,
        VariantProductRatioInterface $variantProductRatio,
        Connection $connection,
        Result $result
    ) {
        $connection->executeQuery(Argument::cetera())->willReturn($result);

        $this->beConstructedWith(
            $legacyVariantProductRatio,
            $variantProductRatio,
            $connection,
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SwitchVariantProductCompletenessRatio::class);
    }

    function it_gets_completenesses_from_new_table(
        VariantProductRatioInterface $legacyVariantProductRatio,
        VariantProductRatioInterface $variantProductRatio,
        Result $result,
    ): void {
        $this->newTableExists($result, true);

        $productModel = new productModel();
        $completeVarianteProducts = new CompleteVariantProducts([]);

        $variantProductRatio->findComplete($productModel)->shouldBeCalled()->willReturn($completeVarianteProducts);
        $legacyVariantProductRatio->findComplete(Argument::cetera())->shouldNotBeCalled();

        $this->findComplete($productModel)->shouldReturn($completeVarianteProducts);
    }

    function it_gets_completenesses_from_legacy_table_if_new_table_does_not_exist(
        VariantProductRatioInterface $legacyVariantProductRatio,
        VariantProductRatioInterface $variantProductRatio,
        Result $result,
    ): void {
        $this->newTableExists($result, false);

        $productModel = new productModel();
        $completeVarianteProducts = new CompleteVariantProducts([]);

        $variantProductRatio->findComplete(Argument::cetera())->shouldNotBeCalled();
        $legacyVariantProductRatio->findComplete($productModel)->shouldBeCalled()->willReturn($completeVarianteProducts);

        $this->findComplete($productModel)->shouldReturn($completeVarianteProducts);
    }

    private function newTableExists(Result $result, bool $exists)
    {
        $result->rowCount()->willReturn($exists ? 1 : 0);
    }
}
