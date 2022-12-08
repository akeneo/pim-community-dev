<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindProductWithAppliedDraftIntegration extends TestCase
{
    private const UUID = 'd73399ee-8e6c-42d8-b709-01238a611691';

    private FindProduct $findProduct;

    /** @test */
    public function it_gets_the_original_product_when_there_is_no_draft(): void
    {
        $this->loginAs('julia');
        $originalProduct = $this->findProduct->withUuid(self::UUID);
        Assert::assertSame('The original text', $originalProduct->getValue('a_text', null, null)?->getData());
    }

    /** @test */
    public function it_gets_a_product_with_draft_changes(): void
    {
        $this->loginAs('mary');
        $productWithDraftValues = $this->findProduct->withUuid(self::UUID);
        Assert::assertSame('The updated text', $productWithDraftValues->getValue('a_text', null, null)?->getData());
    }

    /** @test */
    public function it_returns_null_if_the_product_does_not_exist(): void
    {
        $this->loginAs('mary');
        Assert::assertNull($this->findProduct->withUuid(Uuid::uuid4()->toString()));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->findProduct = $this->get('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\FindProductWithAppliedDraft');

        /** @var CategoryAccessInterface $categoryAccess */
        $categoryAccess = $this->get('pimee_security.repository.category_access')->findOneByIdentifier('categoryA.All');
        $categoryAccess->setOwnItems(false);
        $this->get('pimee_security.saver.product_category_access')->save($categoryAccess);

        $this->get('feature_flags')->enable('proposal');
        $product = $this->createProduct(
            'julia',
            Uuid::fromString(self::UUID),
            [
                new SetCategories(['categoryA']),
                new SetFamily('familyA'),
                new SetIdentifierValue('sku', 'my_new_product'),
                new SetTextValue('a_text', null, null, 'The original text')
            ]
        );
        $this->createEntityWithValuesDraft('mary', $product, [
            'values' => [
                'a_text' => [
                    ['data' => 'The updated text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $username, UuidInterface $uuid, array $userIntents): ProductInterface
    {
        $userId = $this->loginAs($username);
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $userId,
                ProductUuid::fromUuid($uuid),
                $userIntents
            )
        );

        return $this->get('pim_catalog.repository.product')->find($uuid);
    }

    private function createEntityWithValuesDraft(
        string $username,
        ProductInterface $product,
        array $changes
    ) : void {
        $this->get('pim_catalog.updater.product')->update($product, $changes);
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $product,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);
    }

    private function loginAs(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertInstanceOf(UserInterface::class, $user);
        $this->get('security.token_storage')->setToken(
            new UsernamePasswordToken($user, 'main', $user->getRoles())
        );

        return (int) $user->getId();
    }
}
