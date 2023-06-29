<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductQueryBuilderTestCase extends TestCase
{
    /** @var Client */
    protected $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(?string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');

        if (null !== $identifier) {
            $command = UpsertProductCommand::createWithIdentifier(
                userId: $this->getUserId('admin'),
                productIdentifier: ProductIdentifier::fromIdentifier($identifier),
                userIntents: $userIntents
            );
        } else {
            $uuid = Uuid::uuid4();
            $command = UpsertProductCommand::createWithUuid(
                userId: $this->getUserId('admin'),
                productUuid: ProductUuid::fromUuid($uuid),
                userIntents: $userIntents
            );
        }
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return null !== $identifier ? $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier) :
            $this->get('pim_catalog.repository.product')->find($uuid);
    }

    /**
     * @param array $data
     */
    protected function createAttribute(array $data)
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param array $data
     */
    protected function createAttributeOption(array $data)
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    /**
     * @param array $data
     */
    protected function createFamily(array $data)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $family_variant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family_variant, $data);
        $constraintList = $this->get('validator')->validate($family_variant);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family_variant);

        return $family_variant;
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters)
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create();

        foreach ($filters as $filter) {
            $context = $filter[3] ?? [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param array $sorters
     * @param array $options
     *
     * @return CursorInterface
     */
    protected function executeSorter(array $sorters, $options = [])
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose')->create($options);

        foreach ($sorters as $sorter) {
            $context = $sorter[2] ?? [];
            $pqb->addSorter($sorter[0], $sorter[1], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert(CursorInterface $result, array $expected)
    {
        $products = [];
        foreach ($result as $product) {
            $products[] = $product->getIdentifier();
        }

        sort($products);
        sort($expected);

        $this->assertSame($expected, $products);
    }

    /**
     * @param CursorInterface $result
     * @param array $expected
     */
    protected function assertOrder(CursorInterface $result, array $expected)
    {
        $products = [];
        foreach ($result as $product) {
            $products[] = $product->getIdentifier();
        }

        $this->assertSame($expected, $products);
    }

    protected function activateLocaleForChannel(string $localeCode, string $channelCode): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        Assert::assertNotNull($channel, sprintf('Channel "%s" not found', $channelCode));

        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier($localeCode);
        Assert::assertNotNull($locale, sprintf('Locale "%s" not found', $localeCode));

        $channel->addLocale($locale);

        $this->get('pim_catalog.saver.channel')->save($channel);
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
