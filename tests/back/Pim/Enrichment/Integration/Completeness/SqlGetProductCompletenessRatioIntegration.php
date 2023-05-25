<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductCompletenessRatioIntegration extends TestCase
{
    /** @var GetProductCompletenesses */
    public $getProductCompletenesses;

    /** @var GetProductCompletenessRatio */
    private $getProductCompletenessRatio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductCompletenesses = $this->get(
            'akeneo.pim.enrichment.product.query.get_product_completenesses'
        );
        $this->getProductCompletenessRatio = $this->get(
            'akeneo.pim.enrichment.product.query.product_completeness_ratio'
        );
    }

    /**
     * @test
     */
    public function it_returns_the_completeness_ratio_of_a_product_for_a_given_channel_and_locale()
    {
        $product = $this->createProduct();
        $completenesses = $this->getProductCompletenesses->fromProductUuid($product->getUuid());

        Assert::assertNotEmpty($completenesses);

        /** @var ProductCompleteness $completeness */
        foreach ($completenesses as $completeness) {
            $ratio = $this->getProductCompletenessRatio->forChannelCodeAndLocaleCode(
                $product->getUuid(),
                $completeness->channelCode(),
                $completeness->localeCode()
            );
            Assert::assertNotNull($ratio);
            Assert::assertEquals($completeness->ratio(), $ratio);
        }
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_completeness_is_not_calculated_yet()
    {
        $product = $this->createProduct();
        $this->get('database_connection')->executeUpdate(
            'DELETE FROM pim_catalog_completeness WHERE product_uuid = :productUuid',
            [
                'productUuid' => $product->getUuid()->getBytes(),
            ]
        );
        $this->get('database_connection')->executeUpdate(
            'DELETE FROM pim_catalog_product_completeness WHERE product_uuid = :productUuid',
            [
                'productUuid' => $product->getUuid()->getBytes(),
            ]
        );
        Assert::assertNull(
            $this->getProductCompletenessRatio->forChannelCodeAndLocaleCode($product->getUuid(), 'ecommerce', 'en_US')
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'test_completeness',
            userIntents: [
                new SetFamily('familyA'),
                new SetDateValue('a_date', null, null, new \DateTime('2020-03-18T00:00:00+00:00')),
                new SetTextValue('a_text', null, null, 'lorem ipsum'),
                new SetBooleanValue('a_yes_no', null, null, false),
                new SetPriceCollectionValue('a_scopable_price', 'ecommerce', null, [
                    new PriceValue('10.00', 'EUR'),
                    new PriceValue('12.00', 'USD'),
                ]),
                new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Lorem ipsum dolor sit amet')
            ]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier('test_completeness');
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }
}
