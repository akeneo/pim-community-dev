<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Group;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindProductUuidsInGroupIntegration extends TestCase
{
    private int $adminId;

    /** @test */
    public function it_returns_an_empty_array_when_the_group_does_not_exist(): void
    {
        Assert::assertSame([], $this->query(-42));
    }

    /** @test */
    public function it_returns_the_uuids_of_the_products_of_a_given_group(): void
    {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];

        $groupA = $this->createGroup('groupA');
        $groupB = $this->createGroup('groupB');
        $groupC = $this->createGroup('groupC');

        $this->upsertProductInGroups($uuids[0], 'product1', ['groupA', 'groupB']);
        $this->upsertProductInGroups($uuids[1], 'product2', ['groupA']);
        $this->upsertProductInGroups($uuids[2], 'product3', ['groupB']);
        $this->upsertProductInGroups($uuids[3], 'product4', []);

        Assert::assertEqualsCanonicalizing([$uuids[0]->toString(), $uuids[1]->toString()], $this->query($groupA->getId()));
        Assert::assertEqualsCanonicalizing([$uuids[0]->toString(), $uuids[2]->toString()], $this->query($groupB->getId()));
        Assert::assertSame([], $this->query($groupC->getId()));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createAdminUser()->getId();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function query(int $groupId): array
    {
        return $this->get('Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup')->forGroupId($groupId);
    }

    private function createGroup(string $groupCode): GroupInterface
    {
        $group = $this->get('pim_catalog.factory.group')->createGroup('RELATED');
        $this->get('pim_catalog.updater.group')->update($group, [
            'code' => $groupCode
        ]);

        $errors = $this->get('validator')->validate($group);
        Assert::assertCount(0, $errors, \sprintf('validation errors: %s', $errors));

        $this->get('pim_catalog.saver.group')->save($group);

        return $group;
    }

    private function upsertProductInGroups(UuidInterface $uuid, string $identifier, array $groupCodes): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->login('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminId,
                ProductUuid::fromUuid($uuid),
                [
                    new SetIdentifierValue('sku', $identifier),
                    new SetGroups($groupCodes)
                ]
            )
        );
    }
}
